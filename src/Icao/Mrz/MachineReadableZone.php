<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Icao\Mrz;

use DateTimeImmutable;
use DateTimeZone;
use LogicException;
use MSSTC4PHP\ValueObject\Documents\Contract\Maskable;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidMrzException;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidPersonNameException;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzCheckDigit;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzDocumentNumber;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzField;
use MSSTC4PHP\ValueObject\Documents\Internal\MrzLayout;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Internal\TwoDigitYearDate;
use MSSTC4PHP\ValueObject\Documents\Sex;
use MSSTC4PHP\ValueObject\Exception\InvalidValueObjectException;
use MSSTC4PHP\ValueObject\I18n\CountryCode;
use MSSTC4PHP\ValueObject\Support\AbstractStringValueObject;
use Override;

/**
 * Machine readable zone of an ICAO Doc 9303 travel document: {@see Td3} (passport, 2 lines of 44), {@see Td2}
 * (2 lines of 36) or {@see Td1} (3 lines of 30). Input is the lines joined by `\n` or `\r\n`, or one string of 88,
 * 72 or 90 characters; the stored form joins the lines by `\n`. Every check digit of the format is enforced, the
 * composite one included. `fromString()` picks the format by the length.
 *
 * Doc 9303 gives no century for the two-digit years: {@see birthDate()} takes the most recent past one,
 * {@see expiryDate()} the one nearest to a reference date; the caller passes the date, there is no hidden clock.
 * `masked()` replaces the document number, the optional data, the birth date and their check digits by `*`, which is
 * not an MRZ character, so a masked MRZ never parses. It does not implement `HasSex` (`sex()` is null for `<`) nor
 * `HasBirthDate` (`birthDate()` takes the reference date).
 *
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p3_cons_en.pdf ICAO Doc 9303 Part 3,
 *      8th ed. (2021), §4.8 (dates), §4.9 (check digits), §5 (codes)
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p4_cons_en.pdf Part 4 (TD3)
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p5_cons_en.pdf Part 5 (TD1)
 * @see https://www.icao.int/sites/default/files/publications/DocSeries/9303_p6_cons_en.pdf Part 6 (TD2)
 * @see docs/sources/ICAO.md "Machine readable zone"
 */
abstract class MachineReadableZone extends AbstractStringValueObject implements Maskable
{
    private const string CHARACTERS = '/^[A-Z0-9<]+$/D';

    /**
     * Returns a {@see Td1}, {@see Td2} or {@see Td3}.
     *
     * @throws InvalidMrzException when no format fits the lines or the format's own rules refuse the value
     */
    public static function fromString(string $value): self
    {
        foreach ([Td3::class, Td2::class, Td1::class] as $class) {
            if (self::split($value, $class::LINES, $class::LINE_LENGTH) !== null) {
                return new $class($value);
            }
        }

        throw new InvalidMrzException('Invalid ICAO MRZ.');
    }

    #[Override]
    public static function from(string $value): static
    {
        $mrz = self::fromString($value);
        if (!$mrz instanceof static) {
            throw new InvalidMrzException('Invalid ICAO MRZ.');
        }

        return $mrz;
    }

    #[Override]
    public static function tryFrom(string $value): ?static
    {
        try {
            return static::from($value);
        } catch (InvalidValueObjectException) {
            return null;
        }
    }

    /**
     * @return non-empty-string the document code without its trailing filler: `P`/`PP`, `I`, `AC`
     */
    public function documentCode(): string
    {
        return $this->trimmed(static::layout()->documentCode, 'A document code starts with a letter.');
    }

    /**
     * @return non-empty-string the raw code without its trailing filler: `UTO`, `D`, `GBD`, `XXA`
     */
    public function issuingState(): string
    {
        return $this->trimmed(static::layout()->issuingState, 'An issuing state is never blank.');
    }

    /**
     * @return non-empty-string|null null when the MRZ leaves the nationality blank
     */
    public function nationality(): ?string
    {
        $code = \rtrim(static::layout()->nationality->of($this->lines()), '<');

        return $code === '' ? null : $code;
    }

    /**
     * The issuing state as an ISO 3166-1 country: alpha-3 codes, and Germany's `D` of Part 3 §5. Codes Part 3 adds
     * outside ISO 3166 (`GBD`, `UNO`, `XXA`, `RKS`) give null.
     */
    public function issuingCountry(): ?CountryCode
    {
        return $this->country($this->issuingState());
    }

    public function nationalityCountry(): ?CountryCode
    {
        $code = $this->nationality();

        return $code === null ? null : $this->country($code);
    }

    public function name(): PersonName
    {
        return new PersonName(static::layout()->name->of($this->lines()));
    }

    /**
     * The number as the document gives it, a long one (Part 5 §4.2.2.1 note j) joined, trailing fillers removed.
     */
    public function documentNumber(): string
    {
        return $this->documentNumberParts()['number'];
    }

    /**
     * The optional data, trailing fillers removed. In a TD3 this is the personal number.
     */
    public function optionalData(): string
    {
        return \rtrim($this->documentNumberParts()['optional'], '<');
    }

    /**
     * @return non-empty-string six characters `YYMMDD`, `<` for an unknown part
     */
    public function birthDateDigits(): string
    {
        return $this->digits(static::layout()->birthDate->of($this->lines()));
    }

    /**
     * @return non-empty-string six digits `YYMMDD`
     */
    public function expiryDateDigits(): string
    {
        return $this->digits(static::layout()->expiryDate->of($this->lines()));
    }

    /**
     * The birth date in the most recent past century: not after `$today` (UTC date). Null when the year, the month
     * or the day is unknown (`<<`).
     */
    public function birthDate(DateTimeImmutable $today): ?DateTimeImmutable
    {
        $parts = $this->dateParts($this->birthDateDigits());
        if ($parts === null) {
            return null;
        }

        [$yearDigits, $month, $day] = $parts;
        $utcToday = $today->setTimezone(new DateTimeZone('UTC'));
        $limit = $utcToday->format('Y-m-d');
        $year = \intdiv((int) $utcToday->format('Y'), 100) * 100 + $yearDigits;
        while ($year >= 1) {
            if (\checkdate($month, $day, $year) && $this->format($year, $month, $day) <= $limit) {
                return $this->date($year, $month, $day);
            }

            $year -= 100;
        }

        return null;
    }

    /**
     * The expiry date in the century that puts it nearest to `$reference`. Doc 9303 gives no rule.
     */
    public function expiryDate(DateTimeImmutable $reference): DateTimeImmutable
    {
        $parts = $this->dateParts($this->expiryDateDigits());
        if ($parts === null) {
            throw new LogicException('An expiry date is always known.');
        }

        [$yearDigits, $month, $day] = $parts;
        $referenceYear = (int) $reference->setTimezone(new DateTimeZone('UTC'))->format('Y');
        $best = null;
        $bestDistance = null;
        foreach ([-100, 0, 100] as $shift) {
            $year = \intdiv($referenceYear, 100) * 100 + $yearDigits + $shift;
            if ($year < 1 || !\checkdate($month, $day, $year)) {
                continue;
            }

            $date = $this->date($year, $month, $day);
            $distance = \abs($date->getTimestamp() - $reference->getTimestamp());
            if ($bestDistance === null || $distance < $bestDistance) {
                $best = $date;
                $bestDistance = $distance;
            }
        }

        return $best ?? throw new LogicException('Some century always holds the date.');
    }

    /**
     * @return Sex|null null for `<` (unspecified)
     */
    public function sex(): ?Sex
    {
        return match (static::layout()->sex->of($this->lines())) {
            'F' => Sex::Female,
            'M' => Sex::Male,
            default => null,
        };
    }

    #[Override]
    public function masked(): string
    {
        $lines = $this->lines();
        foreach (static::layout()->masked as $field) {
            $lines[$field->line - 1] = \substr_replace(
                $lines[$field->line - 1],
                \str_repeat('*', $field->length),
                $field->position - 1,
                $field->length,
            );
        }

        return Text::nonEmpty(\implode("\n", $lines));
    }

    abstract protected static function layout(): MrzLayout;

    /**
     * @param list<string> $lines
     *
     * @throws InvalidMrzException
     */
    protected static function assertFormatSpecific(array $lines): void
    {
    }

    #[Override]
    protected function parse(string $value): string
    {
        $layout = static::layout();
        $lines = self::split($value, $layout->lines, $layout->lineLength)
            ?? throw new InvalidMrzException('Invalid ICAO MRZ.');

        $this->assertFields($lines, $layout);
        static::assertFormatSpecific($lines);
        $this->assertCheckDigits($lines, $layout);

        return \implode("\n", $lines);
    }

    /**
     * @param int<1, max> $lines
     * @param int<1, max> $length
     *
     * @return list<string>|null
     */
    private static function split(string $value, int $lines, int $length): ?array
    {
        if (\str_contains($value, "\n")) {
            $parts = \preg_split('/\r?\n/', $value);
        } else {
            $parts = \strlen($value) === $lines * $length ? \str_split($value, $length) : false;
        }

        if ($parts === false || \count($parts) !== $lines) {
            return null;
        }

        foreach ($parts as $part) {
            if (\strlen($part) !== $length || \preg_match(self::CHARACTERS, $part) !== 1) {
                return null;
            }
        }

        return $parts;
    }

    /**
     * @param list<string> $lines
     */
    private function assertFields(array $lines, MrzLayout $layout): void
    {
        $sex = $layout->sex->of($lines);
        $nationality = $layout->nationality->of($lines);
        $valid = \preg_match($layout->documentCodePattern, $layout->documentCode->of($lines)) === 1
            && \preg_match('/^[A-Z]{1,3}<{0,2}$/D', $layout->issuingState->of($lines)) === 1
            && ($nationality === '<<<' || \preg_match('/^[A-Z]{1,3}<{0,2}$/D', $nationality) === 1)
            && \in_array($sex, ['F', 'M', '<'], true)
            && $this->knownDate($layout->expiryDate->of($lines), false)
            && $this->knownDate($layout->birthDate->of($lines), true);

        if (!$valid) {
            throw new InvalidMrzException('Invalid ICAO MRZ field.');
        }

        try {
            new PersonName($layout->name->of($lines));
        } catch (InvalidPersonNameException) {
            throw new InvalidMrzException('Invalid ICAO MRZ name.');
        }
    }

    /**
     * @param list<string> $lines
     */
    private function assertCheckDigits(array $lines, MrzLayout $layout): void
    {
        $number = MrzDocumentNumber::resolve(
            $layout->documentNumber->of($lines),
            $layout->documentNumberCheck->of($lines),
            $layout->optionalData->of($lines),
            $layout->longDocumentNumber,
        );
        $composite = '';
        foreach ($layout->compositeData as $field) {
            $composite .= $field->of($lines);
        }

        if ($number === null
            || !MrzCheckDigit::matches($layout->birthDate->of($lines), $layout->birthDateCheck->of($lines))
            || !MrzCheckDigit::matches($layout->expiryDate->of($lines), $layout->expiryDateCheck->of($lines))
            || !MrzCheckDigit::matches($composite, $layout->compositeCheck->of($lines))) {
            throw new InvalidMrzException('Invalid ICAO MRZ check digit.');
        }
    }

    private function knownDate(string $digits, bool $partlyUnknown): bool
    {
        $parts = $this->splitDigits($digits, $partlyUnknown);
        if ($parts === null) {
            return false;
        }

        [$year, $month, $day] = $parts;
        if ($month !== null && ($month < 1 || $month > 12) || $day !== null && ($day < 1 || $day > 31)) {
            return false;
        }

        return $month === null || $day === null || TwoDigitYearDate::exists($day, $month, $year ?? 0);
    }

    /**
     * @return array{int|null, int|null, int|null}|null
     */
    private function splitDigits(string $digits, bool $partlyUnknown): ?array
    {
        $pattern = $partlyUnknown ? '(?:[0-9]{2}|<<)' : '[0-9]{2}';
        if (\preg_match('/^(' . $pattern . ')(' . $pattern . ')(' . $pattern . ')$/D', $digits, $match) !== 1) {
            return null;
        }

        return [
            $match[1] === '<<' ? null : (int) $match[1],
            $match[2] === '<<' ? null : (int) $match[2],
            $match[3] === '<<' ? null : (int) $match[3],
        ];
    }

    /**
     * @return array{int, int, int}|null year digits, month, day; null when a part is unknown
     */
    private function dateParts(string $digits): ?array
    {
        $parts = self::splitDigits($digits, true);
        if ($parts === null) {
            return null;
        }

        [$year, $month, $day] = $parts;

        return $year === null || $month === null || $day === null ? null : [$year, $month, $day];
    }

    /**
     * @return array{number: string, optional: string}
     */
    private function documentNumberParts(): array
    {
        $layout = static::layout();
        $lines = $this->lines();

        return MrzDocumentNumber::resolve(
            $layout->documentNumber->of($lines),
            $layout->documentNumberCheck->of($lines),
            $layout->optionalData->of($lines),
            $layout->longDocumentNumber,
        ) ?? throw new LogicException('A parsed MRZ has a valid document number.');
    }

    /**
     * @return non-empty-string
     */
    private function trimmed(MrzField $field, string $invariant): string
    {
        $code = \rtrim($field->of($this->lines()), '<');

        return $code === '' ? throw new LogicException($invariant) : $code;
    }

    /**
     * @return non-empty-string
     */
    private function digits(string $digits): string
    {
        return $digits === '' ? throw new LogicException('A date has six characters.') : $digits;
    }

    /**
     * @return list<string>
     */
    private function lines(): array
    {
        return \explode("\n", $this->value);
    }

    private function country(string $code): ?CountryCode
    {
        if ($code === 'D') {
            return CountryCode::DE;
        }

        foreach (CountryCode::cases() as $country) {
            if ($country->alpha3() === $code) {
                return $country;
            }
        }

        return null;
    }

    private function format(int $year, int $month, int $day): string
    {
        return \sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    private function date(int $year, int $month, int $day): DateTimeImmutable
    {
        return new DateTimeImmutable($this->format($year, $month, $day) . ' 00:00:00', new DateTimeZone('UTC'));
    }
}
