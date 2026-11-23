<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard;

use MSSTC4PHP\ValueObject\Documents\Contract\HasSex;
use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Exception\InvalidDocumentNumberException;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use MSSTC4PHP\ValueObject\Documents\Internal\Text;
use MSSTC4PHP\ValueObject\Documents\Sex;
use Override;

/**
 * Québec: the numéro d'assurance maladie (NAM), "Gabarit : XXXX99999999": "des trois premières lettres du nom, de la
 * première lettre du prénom, des deux derniers chiffres de l'année de naissance, du mois de naissance (additionné de
 * 50 pour les femmes), du jour de la naissance et, en fin, d'un chiffre valideur" (`YUXJ75021213`). The month is
 * checked against that encoding (01-12 or 51-62) and the day against 01-31; the "chiffre valideur" has no published
 * algorithm and is not verified. A temporary NAM (`M` in the eleventh position) is on no card and is refused.
 *
 * {@see Sex()} reads the month as the MSSS does (`Male` for 01-12, `Female` for 51-62); the card's X gender marker
 * does not change the NAM. The year has two digits and no century, so there is no birth date, only its parts.
 * GIU REQ-19-53 says the sex and birth date fields "ne doit pas être alimenté automatiquement par le NAM lorsque ce
 * dernier est saisi manuellement": the decoded fields are a reading aid, not values to fill a record with from a
 * hand-typed NAM.
 *
 * @see https://publications.msss.gouv.qc.ca/msss/fichiers/2024/24-715-08W.pdf Québec MSSS, "Cadre normatif pour la
 *      gestion de l'identification de l'usager (GIU)" v5.5, element 30100030, REQ-19-53
 * @see https://cdn-contenu.quebec.ca/cdn-contenu/sante/documents/Systeme_et_services_de_sante/regimes-assurance/feuillet_marqueur_X.pdf
 *      Québec MSSS, "Feuillet — carte d'assurance maladie avec le marqueur de genre X"
 * @see docs/sources/CA.md "Health card numbers — per jurisdiction"
 */
final class QcHealthCardNumber extends AbstractHealthCardNumber implements HasSex
{
    private const int FEMALE_MONTH_OFFSET = 50;

    private const string BIRTH_MONTH_DAY = '/^(?:0[1-9]|1[0-2]|5[1-9]|6[0-2])(?:0[1-9]|[12][0-9]|3[01])$/D';

    #[Override]
    public function sex(): Sex
    {
        return \str_contains('56', $this->value[6]) ? Sex::Female : Sex::Male;
    }

    /**
     * @return int<1, 31>
     */
    public function birthDay(): int
    {
        // parse() has validated the day; the clamp only narrows the type for PHPStan.
        return \max(1, \min(31, (int) \substr($this->value, 8, 2)));
    }

    /**
     * @return int<1, 12>
     */
    public function birthMonth(): int
    {
        return \max(1, \min(12, (int) \substr($this->value, 6, 2) % self::FEMALE_MONTH_OFFSET));
    }

    /**
     * @return non-empty-string the last two digits of the birth year; the number holds no century
     */
    public function birthYearDigits(): string
    {
        return Text::nonEmpty(\substr($this->value, 4, 2));
    }

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::QC;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[A-Z]{4}[0-9]{8}');
    }

    #[Override]
    protected function stored(string $normalized, string $document): string
    {
        if (\preg_match(self::BIRTH_MONTH_DAY, \substr($normalized, 6, 4)) !== 1) {
            throw new InvalidDocumentNumberException(\sprintf('Invalid %s (invalid birth date).', $document));
        }

        return $normalized;
    }
}
