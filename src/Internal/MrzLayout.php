<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Internal;

/**
 * Where the data elements of one MRZ format are, per Doc 9303 Parts 4, 5 and 6.
 *
 * @internal
 */
final readonly class MrzLayout
{
    /**
     * @param positive-int $lines
     * @param positive-int $lineLength
     * @param non-empty-string $documentCodePattern PCRE for the two document code characters
     * @param list<MrzField> $compositeData the characters the composite check digit covers, in order
     * @param list<MrzField> $masked the characters `masked()` replaces
     */
    public function __construct(
        public int $lines,
        public int $lineLength,
        public string $documentCodePattern,
        public MrzField $documentCode,
        public MrzField $issuingState,
        public MrzField $name,
        public MrzField $documentNumber,
        public MrzField $documentNumberCheck,
        public bool $longDocumentNumber,
        public MrzField $nationality,
        public MrzField $birthDate,
        public MrzField $birthDateCheck,
        public MrzField $sex,
        public MrzField $expiryDate,
        public MrzField $expiryDateCheck,
        public MrzField $optionalData,
        public MrzField $compositeCheck,
        public array $compositeData,
        public array $masked,
    ) {
    }
}
