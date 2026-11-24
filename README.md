# msstc4php/vo-documents

Value objects for identity documents and personal names of the world's largest economies, with ICAO Doc 9303 Latin
names. Built on [`msstc4php/vo-base`](https://github.com/msstc4php/vo-base); PHP >= 8.4. Today: the Russian Federation,
Australia, Germany, France, Italy, Spain, the Netherlands, Switzerland, the United Kingdom, Türkiye, the United States,
Canada, Mexico, Brazil, China, Japan, South Korea, India, Indonesia and Saudi Arabia (names, national documents and,
where an official format is published, subnational ones).

Every format, checksum and table cites its official source on the class (`@see`); the full notes, with quotes and the
places where the sources disagree with the obvious guess, are in [`docs/sources/`](docs/sources).

Status: stable since 1.0.0; see "Backward compatibility" for what 1.x promises.

## Install

Not on Packagist yet; register the GitHub repositories:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/msstc4php/vo-base" },
        { "type": "vcs", "url": "https://github.com/msstc4php/vo-documents" }
    ],
    "require": {
        "msstc4php/vo-documents": "^1.0"
    }
}
```

Runtime requirements: `msstc4php/vo-base ^1.4`, `ext-mbstring`, `lib-pcre >=10.40` (the `\p{sc=Latin}` script
syntax of the name classes), `symfony/polyfill-intl-normalizer` (`ext-intl` is not needed: the polyfill supplies
`Normalizer`, the only intl API used). No framework.
vo-base 1.4 maps the abstract `MachineReadableZone`. Since vo-base 1.3, string value objects are validated again on
`unserialize()`. Composite names serialize as their codec payload and are rebuilt through the codec.

## Document numbers

```php
use MSSTC4PHP\ValueObject\Documents\Document\RU\Snils;

$snils = Snils::from('112 233 445 95');   // spaces, hyphens, tabs, NBSP are stripped
(string) $snils;                          // "11223344595"  (the full value, always)
$snils->formatted();                      // "112-233-445 95"
$snils->masked();                         // "***-***-*45 95"
Snils::tryFrom('112-233-445-96');         // null (checksum)
```

`from()` throws a subclass of `InvalidValueObjectException` (`InvalidDocumentNumberException`) for invalid input. The
message names the document and a fixed reason ("Invalid SNILS (wrong length).", "Invalid SNILS checksum.");
**it never contains the input or any part of it, not even its length**.

A number typed on a CJK or Arabic keyboard reads as the ASCII one: every document number folds fullwidth digits and
letters (`１２３`, `Ｘ`; letters are upper-cased) and Arabic-Indic digits (U+0660-0669, U+06F0-06F9) to ASCII, and
strips the ideographic space U+3000 with the other separators (space, hyphen, tab, NBSP, U+2007, U+202F, the CJK hyphen
look-alikes U+FF0D, U+FF70, U+30FC, U+2010, U+2011, U+2212, and the invisible bidi marks U+200E, U+200F, U+061C,
U+202A-202E and U+2066-2069 that a number picks up when pasted from right-to-left text). Devanagari digits are not
folded. Names do not fold: a compatibility character in a name is refused.

`__toString()` and `jsonSerialize()` return the full canonical value. Masking is an explicit, separate call,
`masked()` (vo-base's interface `MSSTC4PHP\ValueObject\Contract\Maskable`). No masking rule is official unless the class says so; the default keeps the
last four characters, and a number of 8 characters or fewer keeps three (CH identity document, GB Northern Ireland
licence). The AU classes keep three (their numbers are at most 10 characters long, `docs/sources/AU.md`).

| Class | Document | Check | Source |
|---|---|---|---|
| `Document\RU\Snils` | SNILS, 11 digits | control number mod 101 (above 001-001-998) | SFR Order No. 1463 of 17.11.2025; PFR checking rules |
| `Document\RU\Inn` | INN of an individual, 12 digits | format only (no check digit verified) | FNS Order ЕД-7-14/559@ (algorithm not published) |
| `Document\RU\InternalPassportNumber` | Internal passport, series + number, 10 digits | shape only | Government Decree No. 2267 of 23.12.2023 |
| `Document\RU\PassportIssuingUnitCode` | Issuing unit code, `NNN-NNN` | shape only | MVD Order No. 773 of 16.11.2020 |
| `Document\RU\InternationalPassportNumber` | Passport for travel abroad, 9 digits | shape only | MVD Orders No. 186 (2021), No. 996 (2019) |
| `Document\RU\DriverLicenceNumber` | Driving licence, `00 00 000000` | shape only | MVD Order No. 365 of 13.05.2009 |
| `Document\RU\DriverLicenceCategory` | Licence categories (enum) | n/a | MVD Order No. 365; Federal Law 196-FZ |
| `Document\RU\OmsPolicyNumber` | OMS policy (ЕНП), 16 digits | HL7 Mod10 (Luhn) | FFOMS Order No. 79 of 07.04.2011 |
| `Document\AU\TaxFileNumber` | Tax file number, 8 or 9 digits (kept as written) | format only (no check digit verified) | ATO (algorithm not public); ANAO Report 37 1998-99 |
| `Document\AU\MedicareCardNumber` | Medicare card, 10 digits | format only (no check digit verified) | Australian Digital Health Agency (algorithm not public) |
| `Document\AU\DriverLicence\{Act,Nsw,Nt,Qld,Sa,Tas,Vic,Wa}DriverLicenceNumber` | Licence number per state or territory | shape only | Home Affairs DVS field rules |
| `Document\AU\Jurisdiction` | The eight states and territories (enum, ISO 3166-2:AU) | n/a | ISO 3166-2:AU |

### Documents by country

"Checksum verified" means the algorithm is quoted from an official source (given in the last column and on the class);
"format only" means the shape is checked and the check digit, if the number has one, is **not** verified because no
official algorithm is published. A number with the right shape is accepted, never refused for an unverifiable check.
"Encoded data" lists what the official source defines inside the number and what the class exposes.

| Class | Document | Check | Encoded data | Source |
|---|---|---|---|---|
| `Document\DE\IdentityCardNumber` | Personalausweis serial number, 9 characters | format only (no check digit of its own) | `authorityCode()` | PAuswG § 2 Abs. 8; PAuswV Anlage 3 |
| `Document\DE\PassportNumber` | Reisepass serial number, 9 characters | format only | none | PassG § 4; PassV Anlage 11 |
| `Document\DE\TaxIdentificationNumber` | Steuer-ID, 11 digits | format only; `hasValidCheckDigit()` reports the ELSTER check digit (ISO 7064 MOD 11,10) | none | StIdV § 1; AO § 139b; ELSTER § 2.2 |
| `Document\DE\PensionInsuranceNumber` | Rentenversicherungsnummer, `NN DDMMYY L SS C` | checksum verified (VKVV § 2) | fields as strings, no typed date or sex | VKVV § 2 |
| `Document\FR\Nir` | NIR (numéro de sécurité sociale), 15 characters | key format only | `HasSex`, birth year digits, `birthMonth()`, `birthPlaceCode()` | INSEE; DSN cahier technique |
| `Document\FR\LegacyIdentityCardNumber` | CNI issued until 2021, 12 characters (digits; `2A`/`2B` in a Corsican place code) | format only | `issueYearDigits()`, `issueMonth()` (not validated), `issuePlaceCode()`, `serialNumber()` | NOR INTD0000001C |
| `Document\FR\PassportNumber` | Passport, `99AA99999` | format only | none | France Titres-ANTS |
| `Document\FR\TaxIdentificationNumber` | Numéro fiscal (SPI), 13 digits | format only | none | OECD TIN note (DGFiP) |
| `Document\FR\DriverLicenceNumber` | Permis de conduire, 12 digits | format only | none | France Titres-ANTS |
| `Document\IT\FiscalCode` | Codice fiscale, 16 characters | checksum verified (DM 23.12.1976 art. 7) | `HasSex`, birth year digits, month, day, `birthPlaceCode()`; omocodia read | DM 23 dicembre 1976 |
| `Document\IT\PassportNumber` | Passport, 2 letters + 7 digits | format only | none | DM 23 marzo 2010 n. 303/13, 303/14 |
| `Document\ES\Dni` | DNI, 8 digits + letter | checksum verified (mod 23) | none | Ministerio del Interior; RD 1065/2007 |
| `Document\ES\Nie` | NIE, `X/Y/Z` + 7 digits + letter | checksum verified; pre-2008 `X` + 8 digits: format only | none | Ministerio del Interior; Orden INT/2058/2008 |
| `Document\ES\SocialSecurityNumber` | Número de afiliación, 12 digits | format only | `provinceCode()` (not range-checked) | TGSS, Sistema RED |
| `Document\NL\Bsn` | Burgerservicenummer, 9 digits | checksum verified (elfproef) | none | Logisch Ontwerp BSN 2025.Q4 |
| `Document\NL\IdentityDocumentNumber` | Passport / ID card / emergency document, 9 characters | format only | none | RvIG Kenmerkenbrochure |
| `Document\CH\AhvNumber` | AHV-Nummer, 13 digits | checksum verified (BSV WL VA/IK Anhang 7) | none | BSV WL VA/IK |
| `Document\CH\IdentityDocumentNumber` | Passport / identity card, 8 characters | format only | none | SR 143.111 Art. 16; fedpol |
| `Document\CH\DriverLicenceNumber` | Führerausweis, 12 or 15 digits | format only | `faberPin()`, `sequenceNumber()` (12 digits only) | ASTRA Weisungen 2023 |
| `Document\GB\NationalInsuranceNumber` | NINO, `AB 12 34 56 C` | prefix rules (no check digit exists) | none | HMRC NIM39110 |
| `Document\GB\PassportNumber` | Passport, 9 digits | format only | none | HMPO Basic Passport Checks |
| `Document\GB\GreatBritainDriverLicenceNumber` | DVLA licence driver number, 16 characters | month and day ranges (not the calendar); check characters not verified | `HasSex`, birth year digits, month, day | DVLA INS57P |
| `Document\GB\NorthernIrelandDriverLicenceNumber` | DVA licence driver number, 8 digits | format only | none | nidirect (DVA) |
| `Document\GB\NhsNumber` | NHS number (England, Wales), 10 digits | checksum verified (Modulus 11) | none | NHS Data Dictionary |
| `Document\GB\HealthAndCareNumber` | H&C number (Northern Ireland), 10 digits | checksum verified; range 320000001-399999999 | none | NHS Data Dictionary |
| `Document\GB\ChiNumber` | CHI number (Scotland), 10 digits | date validated; check digit format only, `hasValidCheckDigit()` reports PHS's modulus 11 or modulus 10 check | `HasSex`, birth year digits, month, day | NHS Data Dictionary; PHS `chi_check()` |
| `Document\TR\IdentityNumber` | T.C. Kimlik No, 11 digits | format only | none (NVİ: encodes nothing) | NVİ; Nüfus Hizmetleri Yönetmeliği Madde 4 |
| `Document\TR\PassportNumber` | Passport, letter + 8 digits | format only | none | Council of the EU PRADO |
| `Document\US\SocialSecurityNumber` | SSN, `123-45-6789` | SSA rules: area not 000/666/900-999, group not 00, serial not 0000 (no check digit exists); masked `***-**-6789` (26 CFR 301.6109-4) | none | SSA; IRM 3.13.5.21 |
| `Document\US\Itin` | ITIN, `9NN-NN-NNNN` | first digit 9, digits 4-5 in the IRS ranges; masked `***-**-1234` (26 CFR 301.6109-4) | none | IRM 3.13.5.22 |
| `Document\US\PassportBookNumber` | Passport book, 9 digits or letter + 7/8 digits | format only | none | State Department; USCIS E-Verify |
| `Document\US\PassportCardNumber` | Passport card, `C` + 8 digits | format only | none | USCIS E-Verify |
| `Document\US\MedicareBeneficiaryIdentifier` | MBI, `1EG4-TE5-MK73` | CMS position layout (no check digit exists) | none | CMS |
| `Document\US\DlId\{Al,Az,Co,Ct,Fl,Hi,Ia,Id,Il,In,Ks,Ky,Ma,Md,Me,Mi,Ms,Nd,Ne,Nj,Nm,Ny,Ok,Pa,Ri,Tx,Vt,Wa,Wi,Wy}DlIdNumber` | DL/ID number of 30 states (one class covers a state's licence and ID card where the source says so) | shape only (no check digit verified; HI, KS, MA, NE, VT, WA letters enforced as quoted) | none (the IL, WA, FL and MI name and birth data is not decoded) | state DMV and police publications; USCIS E-Verify RIDE fact sheets |
| `Document\US\Jurisdiction` | The 50 states and DC (enum, ISO 3166-2:US) | n/a | n/a | ISO 3166-2:US |
| `Document\US\DlIdNumber`, `Document\US\DlIdNumberFactory` | Interface of the 30 DL/ID classes; factory `fromNumberIn()` / `jurisdictionsMatching()` | n/a | n/a | the DL/ID sources above |
| `Document\CA\SocialInsuranceNumber` | SIN, `123 456 789` | format only; `hasValidCheckDigit()` reports the CRA check digit (Luhn; the CRA says to keep a failing SIN) | `isTemporaryResident()` (first digit 9, ESDC) | ESDC; CRA |
| `Document\CA\PassportNumber` | Passport, letter + 6 digits + 2 letters (since 2023-05-10) or 2 letters + 6 digits | format only | none | IRCC |
| `Document\CA\DriverLicence\{Ab,Bc,Ns,On,Qc,Sk}DriverLicenceNumber` | Driver's licence number of 6 jurisdictions (NS and SK also cover the photo ID card): AB `123456-789`, BC and SK 1-8 digits stored zero-padded to 8, NS Master Number `FINCH220867001`, ON 15 alphanumerics, QC `L1531-171274-08` | shape only (no check digit published); NS day and month checked as calendar fields | none (the NS surname and birth fields are not decoded: two-digit year) | Service Alberta RDN; ICBC; Service Nova Scotia; MTO glossary; SAAQ; Government of Saskatchewan, SGI |
| `Document\CA\HealthCard\{Ab,Bc,Mb,Nb,Nl,Ns,Nt,Nu,On,Pe,Qc,Sk,Yt}HealthCardNumber` | Health card number of all 13 jurisdictions (8-12 characters; NT letter `D`/`N`/`H`/`M`/`T` + 7 digits; QC NAM `YUXJ75021213`) | BC leading 9 and MOD-11, ON leading digit 1-9 and MOD 10 verified; QC month (01-12, 51-62) and day checked; the rest format only | ON: `OnHealthCardNumber::fromCard()` splits the printed card into the number and an `OnHealthCardVersionCode` (`from()` refuses the version code); QC `HasSex`, birth year digits, month, day (GIU REQ-19-53: a reading aid, not a value to fill a record with) | Ontario Ministry of Health "Province and Territory Codes" and HCV/IHCS specifications; BC Teleplan; Québec MSSS GIU; provincial ministries |
| `Document\CA\Jurisdiction` | The 10 provinces and 3 territories (enum, ISO 3166-2:CA) | n/a | n/a | ISO 3166-2:CA |
| `Document\CA\DriverLicenceNumber`, `DriverLicenceNumberFactory`, `HealthCardNumber`, `HealthCardNumberFactory` | Interfaces of the 6 licence and 13 health card classes, and their factories | n/a | n/a | the provincial sources above |
| `Document\MX\Curp` | CURP, 18 characters (`HEGJ561231HDFRRN04`) | RENAPO layout: letters/digits per position, sex `H`/`M`/`X`, character 17 `0-9` (born up to 1999) or `A-J` (from 2000), birth date must exist; check digit format only (algorithm not published) | `HasBirthDate` (century from character 17), `HasSex` (`X` → `Sex::Unspecified`), `birthStateCode()` (RENAPO code, not ISO) | RENAPO Instructivo Normativo (DOF 2018, 2021); RENAPO Reglas 2021; INE DERFE |
| `Document\MX\ElectorKey` | Clave de elector of the INE voter card, 18 characters | INE layout: 6 letters, `AAMMDD` (month 01-12, day 01-31; not the calendar), 2 state characters, a sex letter, 3 homoclave characters (no check verified) | birth year digits, month, day; `birthStateCode()` and `sexCode()` as written (no value list published) | INE lineamientos 2021; INE glossary |
| `Document\MX\Rfc` | RFC of a natural person, 13 characters (`HEGJ5612311A2`) | SAT `t_RFC_PF` pattern (`A-Z&Ñ`, month and day ranges, final digit or `A`; only ASCII and `ñ` upper-cased); homoclave not verified | none | SAT CFDI data types (`tdCFDI.xsd`) |
| `Document\MX\SocialSecurityNumber` | NSS (IMSS), 11 digits | format only (check digit algorithm not published) | none | IMSS norm 3000-001-007 |
| `Document\MX\DriverLicence\MexDriverLicenceNumber` | Licencia de conducir of the Estado de México, 12 digits | format only (no check digit or encoding published) | none | Secretaría de Movilidad del Estado de México, licence validator |
| `Document\MX\Jurisdiction` | The 32 federal entities (enum, ISO 3166-2:MX) | n/a | n/a | ISO 3166-2:MX |
| `Document\MX\DriverLicenceNumber`, `Document\MX\DriverLicenceNumberFactory` | Interface of the one covered licence class and its factory | n/a | n/a | the Edomex validator above |
| `Document\BR\Cpf` | CPF, `280.012.389-38` | Receita Federal "DV módulo 11" (both check digits); a CPF of one repeated digit is accepted (no Receita Federal text refuses it) | `fiscalRegion()` (the ninth digit, a Receita Federal region spanning several states), `formatted()` | Receita Federal ADE COFIS nº 41/2017, Anexo II; Receita Federal CPF leaflet |
| `Document\BR\VoterRegistrationNumber` | Número de inscrição eleitoral (título de eleitor), up to 12 digits, stored zero-padded to 12 | TSE state code `01`-`28`; check digits format only (TSE names "Módulo 11" without weights) | `stateCode()` (TSE code, not ISO: `28` is abroad), `isAbroad()` | TSE Resolução nº 23.659/2021, art. 36 |
| `Document\BR\Nis` | NIS (PIS, PASEP, NIT), 11 digits (`118.16788.89-3`) | check digit verified (ANS `isDvPisPasepValido`: weights 2-9 from the right, `11 - sum % 11`, 10 or 11 → 0); repeated digits accepted | `formatted()` | Manual de Orientação do eSocial 2.4; ANS "Algoritmos do Aplicativo de Carga"; SIPREV (display form) |
| `Document\BR\Cns` | Cartão Nacional de Saúde, 15 digits | check verified (ANS `isDvCnsValido`: digits weighted 15 to 1 sum to a multiple of 11, all zeros refused) | none | DATASUS CADSUS integration specification; ANS "Algoritmos do Aplicativo de Carga" |
| `Document\BR\DriverLicenceNumber` | CNH número do registro nacional, 9 characters + 2 check digits | format only (CONTRAN names "módulo 11" without weights); letters accepted in the first nine ("caracteres") | none | CONTRAN Resoluções nº 1.020/2025 art. 10 and nº 886/2021 art. 4 |
| `Document\BR\PassportNumber` | Passport, 2 letters ("série") + 6 digits (`CS265436`) | format only | `series()` | Polícia Federal |
| `Document\BR\IdentityCard\SpIdentityCardNumber` | RG of the São Paulo Carteira de Identidade (IIRGD), 1-8 digits + check character (`12.345.678-2`) | check character verified (SSP-SP form's `checaRG`: weights 2-9 from the right, sum % 11, 10 → `X`); all zeros refused | none | SSP-SP Atestado de Antecedentes Criminais form and `aac.js` |
| `Document\BR\Jurisdiction` | The 27 federative units (enum, ISO 3166-2:BR) | n/a | n/a | ISO 3166-2:BR |
| `Document\BR\IdentityCardNumber`, `Document\BR\IdentityCardNumberFactory` | Interface of the one covered RG class and its factory | n/a | n/a | the SSP-SP form above |
| `Document\CN\CitizenIdentityNumber` | 公民身份号码, 18 characters (also the number of the Hong Kong, Macao and Taiwan resident permit) | checksum verified (ISO 7064 MOD 11-2, `x` stored as `X`); birth date must exist; the 15-digit number is refused | `HasBirthDate`, `HasSex` (odd sequence code: man), `administrativeDivisionCode()` (GB/T 2260 string, not ISO) | GB 11643-1999; Resident Identity Card Law |
| `Document\CN\ForeignerPermanentResidenceNumber` | Foreigner's permanent residence ID card since 2023-12-01, 18 characters starting with 9 | checksum verified (MOD 11-2, "与公民身份号码校验方式相同"); birth date must exist | `HasBirthDate`, `applicationPlaceCode()`, `nationalityCode()` (strings: the code lists are not named) | NIA notice on the new card |
| `Document\CN\LegacyForeignerPermanentResidenceNumber` | Foreigner's permanent residence permit and 2017 ID card, 3 letters + 12 digits | format only (no check algorithm named; two-digit year) | `nationalityCode()` (the 3 letters) | NIA |
| `Document\CN\OrdinaryPassportNumber` | Ordinary passport: `E` + 8 digits, `E` + letter + 7 digits (from April 2017), `G` + 8 digits | format only | none | NIA |
| `Document\CN\ExitEntryPermitToHongKongMacauNumber` | 往来港澳通行证, `C` + 8 digits or `C` + letter + 7 digits (from 2025-10-15) | format only | none | NIA |
| `Document\CN\ExitEntryPermitToTaiwanNumber` | 往来台湾通行证, `L` + 8 digits (older permit `T` + 8 digits) | format only | none | NIA |
| `Document\JP\MyNumber` | 個人番号, 12 digits | checksum verified (総務省令 Art. 5) | none | 番号法施行令 Art. 8; 総務省令 Art. 5 |
| `Document\JP\BasicPensionNumber` | 基礎年金番号, 10 digits | format only | none | 日本年金機構 |
| `Document\JP\ResidenceCardNumber` | 在留カード番号, 2 letters + 8 digits + 2 letters | format only | none | 厚生労働省 (島根労働局) filing instructions |
| `Document\JP\PassportNumber` | Passport, 2 letters + 7 digits | format only | none | 外務省 |
| `Document\JP\DriverLicenceNumber` | 運転免許証の番号, 12 digits (the マイナ免許証 number has the same layout) | check digit not verified (the notice names "モジュラス11" without weights) | `publicSafetyCommissionCode()` (string, not ISO), `yearSymbol()`, `serialNumber()`, `reissueSymbol()` | 警察庁丁運発第76号 (2025-03-10) |
| `Document\KR\ResidentRegistrationNumber` | 주민등록번호, 13 digits | birth date must exist in the century of the 7th digit; no check digit verified (random digits since 2020-10) | `HasBirthDate`, `HasSex` | 주민등록법 시행규칙 Art. 2; 국가기록원; 행정안전부 (2017 주민등록 사무편람 for 9/0) |
| `Document\KR\ForeignerRegistrationNumber` | 외국인등록번호, 13 digits (7th digit 5-8) | month 01-12, day 01-31 (no official century, not the calendar); no check digit | `HasSex` (법무부: 5 and 7 man, 6 and 8 woman), `birthYearDigits()`, `birthMonth()`, `birthDay()` | 출입국관리법 시행규칙 별표 2; 법무부 release of 2023-03-28 |
| `Document\KR\MultipleEntryPassportNumber` | Multiple-entry (복수) `M` series: `M` + 8 digits, or `M` + 3 digits + letter + 4 digits (from 2021-12-21) | format only | none | 외교부 여권안내 |
| `Document\KR\DriverLicenceNumber` | 운전면허번호: 12 digits (from 2014-07-02) or the region name + 10 digits | region name checked against KoROAD's table; no check digit; both forms masked as 12 digits, `********3361` | `regionCode()` (string, not ISO), `formatted()` | 도로교통공단 (KoROAD) |
| `Document\IN\AadhaarNumber` | Aadhaar, 12 digits | Verhoeff check digit (UIDAI's own paper *recommends* it); no first-digit rule | none; `masked()` is UIDAI's `XXXX XXXX 1234` | UIDAI "A UID Numbering Scheme" (2010) |
| `Document\IN\PermanentAccountNumber` | PAN, 10 characters (`BLGPS1234D`) | layout of the Income Tax Department tutorial (`0000` refused); the check letter is not verified | `holderType()` (`PanHolderType`, the 4th character) | Income Tax Department |
| `Document\IN\PassportNumber` | Passport, 8 characters | length and ASCII letters and digits only | none | MEA passport data page structure |
| `Document\IN\AbhaNumber` | ABHA number, 14 digits | format only | none | PIB explainer, Ministry of Health and Family Welfare |
| `Document\ID\NationalIdentityNumber` | NIK, 16 digits (also the driving licence number since July 2024) | day 01-31 (a woman's plus 40: 41-71) and month 01-12; the calendar is not checked (no official rule) | `HasSex`, `regionCode()` (string, not ISO), `birthDay()`, `birthMonth()`, `birthYearDigits()` | Ditjen Dukcapil |
| `Document\ID\TaxpayerNumber` | NPWP, 15 digits (`99.999.999.9-999.999`) or 16 digits; all zeros refused; a 15-digit number and its `0`-prefixed 16-digit form are two values, not `equals()` | format only | `isFifteenDigits()`, `formatted()` (15 digits) | DJP PENG-6/PJ.09/2024; PMK 112/2022 |
| `Document\ID\FamilyCardNumber` | Nomor Kartu Keluarga, 16 digits | format only | none | Ditjen Dukcapil |
| `Document\SA\NationalIdentityNumber` | National ID number, 10 digits starting with 1; **also the driving licence number** | ten digits; the first digit routes (ZATCA BR-KSA-F-10, a warning there) and is not checked; no check digit | none | ZATCA SDK v3.4.4 announcement |
| `Document\SA\IqamaNumber` | Iqama number, 10 digits starting with 2; **also the driving licence number** | ten digits; the first digit routes (ZATCA BR-KSA-F-11) and is not checked; no check digit | none | ZATCA SDK v3.4.4 announcement |
| `Document\SA\IdentityNumberFactory` | Routes a bare number by its first digit: `fromNumber()` / `tryFromNumber()` | `1` national ID, `2` iqama; any other first digit refused (never guessed); candidate set frozen for 1.x | n/a | ZATCA BR-KSA-F-10/F-11 |

Names: `Name\<CC>\FamilyName`, `GivenName` and `PersonName` for DE, FR, IT, ES, NL, CH, GB, TR, US, CA, MX and BR, like
the RU and AU ones; the Asian ones: see "Asian documents and names" below. Spain's `PersonName` has two surnames
(`firstSurname`, optional `secondSurname`; 0.x called them `first` and `second`, and stored payloads with those keys
still load); in the Dutch one the prefixes (`van der`) are part of the family name; the Swiss, Dutch,
British, US and Canadian ones accept a person without a given name. The US one is `Name\US\PersonName($family, ?$given,
?$middle, ?$suffix)` (`given` null for a one-word name), the suffix a `Name\US\NameSuffix` (the AAMVA list `JR`, `SR`,
`1ST`/`I` … `9TH`/`IX`), written last among the secondary identifiers of the ICAO form, ordinals as Roman numerals
(`SMITH<<JOHN<MICHAEL<III`). The Canadian one is `Name\CA\PersonName($family, ?$given)` (`given` null for a one-word
name; IRCC: the name goes in the surname field), letters of the Latin script (English and French names). The Mexican one
is `Name\MX\PersonName(?$firstSurname, ?$secondSurname, $given)`: two surnames, one, or none (RENAPO records persons without surnames;
then the given names are the only ICAO identifier, `JUAN`). The Brazilian one is `Name\BR\PersonName($family, $given)`:
both required (CNJ Código Nacional de Normas art. 515-B), each part holding several words with particles in the case
given (`da Silva`, `Maria da Conceição`).

Contracts: vo-base's `Contract\Maskable` (every number above and the MRZ; the package's own `Contract\Maskable` of 0.x is removed in 1.0), `Contract\Subdivision` and `Contract\IssuedBySubdivision` (AU,
US, CA, MX and BR jurisdictions, AU licences, US DL/ID numbers, CA licence and health card numbers, MX licence numbers,
BR RG numbers). `Contract\HasSex` and the `Sex` enum are implemented by the documents that encode a sex
(`Nir`, `FiscalCode`, the DVLA licence number, `ChiNumber`, the Québec NAM, the Mexican CURP, the Chinese citizen
identity number, the Korean resident and foreigner registration numbers, the Indonesian NIK, the ICAO MRZ);
`Sex::Unspecified` is the CURP's `X` (RENAPO, non-binary persons) and the MRZ filler `<` (Doc 9303 "< = unspecified"). `Contract\HasBirthDate` is implemented by the Mexican CURP (its 17th
character gives the century), the Chinese citizen identity number and 2023 permanent residence number (a full
`YYYYMMDD`) and the Korean resident registration number (the 7th digit gives the century: 국가기록원, 행정안전부 사무편람); the other
encoded birth dates lack an officially stated century, so those classes expose the year digits, month and day instead
(the NIK, the Korean foreigner registration number: `birthYearDigits()`). Both check only the day and month ranges
(30 February is accepted; see "Birth dates" under "Backward compatibility").

### Asian documents and names

```php
use MSSTC4PHP\ValueObject\Documents\Document\CN\CitizenIdentityNumber;
use MSSTC4PHP\ValueObject\Documents\Document\SA\IdentityNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\SA\IqamaNumber;
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\CN\{FamilyName, GivenName, PersonName};
use MSSTC4PHP\ValueObject\Documents\Name\JP;

$id = CitizenIdentityNumber::from('１１０１０５１９４９１２３１００２Ｘ');   // fullwidth digits fold: 11010519491231002X
$id->birthDate();                                                   // 1949-12-31 00:00 UTC
$id->sex();                                                         // Sex::Female
IqamaNumber::from('٢٣٤٥٦٧٨٩٠١');                                    // Arabic-Indic digits fold: 2345678901
IdentityNumberFactory::fromNumber('1012345678');                    // NationalIdentityNumber (first digit 1; 2: IqamaNumber)
IdentityNumberFactory::tryFromNumber('3012345678');                 // null: first digit neither 1 nor 2

// China, Korea, Indonesia, Saudi Arabia, Indian Devanagari: the Latin form of the passport is required, never derived.
$name = new PersonName(FamilyName::from('张'), GivenName::from('伟'), IcaoPersonName::from('ZHANG<<WEI'));
$whole = new PersonName(FamilyName::from('阿沛·阿旺晋美'), null, IcaoPersonName::from('NGAPOI<NGAWANG<JIGME'));

// Japan: the derived Latin form is the MOFA Hepburn spelling of the readings.
$jp = new JP\PersonName(
    JP\FamilyName::from('山田'),
    JP\GivenName::from('太郎'),
    JP\FamilyNameReading::from('やまだ'),
    JP\GivenNameReading::from('たろう'),
);
(string) $jp->latin();   // "YAMADA<<TARO"
```

- **Driving licences of CN, SA and ID have no class of their own.** The Chinese licence number is the holder's citizen
  identity number (use `Document\CN\CitizenIdentityNumber`), and the Saudi licence carries the national ID or iqama
  number (use `Document\SA\NationalIdentityNumber` or `Document\SA\IqamaNumber`). The Indonesian SIM number is the NIK
  since July 2024 (use `Document\ID\NationalIdentityNumber`). JP and KR have a licence class.
- **A name where the passport spelling is a personal choice never gets a guessed Latin form** (CN pinyin against
  Cantonese or Wade-Giles, KR Kim or Gim, SA, ID, and the Devanagari names of IN). `Name\CN\PersonName` and
  `Name\KR\PersonName` take `(family, ?given, latin)`; a name with no family and given split (a CN minority-nationality
  name, a KR foreigner's name kept after naturalisation) is stored whole in `family` with `given` null.
  `Name\ID\PersonName` is one `FullName` (Dukcapil records a single "Nama Lengkap") and the Latin form.
  `Name\SA\PersonName` takes the given name, the optional `father`, `grandfather` and `family` names and the Latin form.
  `Name\IN\Devanagari\PersonName` takes `(given, ?surname, latin)`. None of them checks the Latin form against the
  national one beyond being a valid ICAO name; equality includes the Latin form.
- **India in Latin letters** is `Name\IN\PersonName(given, ?surname, ?latin)`, separate from the Devanagari one (the
  Passport Seva form: a person with no surname writes the whole name in the given part, ICAO `RAJESH<KUMAR`, the Doc
  9303 single-name rule, although Passport Seva prints such a passport with a blank surname, `P<IND<<RAJESH<KUMAR`,
  which cannot be stored; derived `JAIN<<PIYUSH<KUMAR`; policy `TransliterationVariant`). Its parts are
  `Name\IN\GivenName` and `Name\IN\Surname`, not `FamilyName`: "Surname" is the Passport Seva form's own term, so it
  stays the class and payload key (`surname`) in 1.x.
- **Japan** takes the 氏名 and the 振り仮名 of each part and derives the passport spelling by MOFA's Hepburn table
  (`ん` is M before B, M and P; `っ` doubles the consonant; the long vowel `ー`, `おう`/`うう` and `おお` are omitted:
  `さとう` is `SATO`). Kana cannot tell a long vowel from a morpheme boundary, so `井上` (いのうえ) is derived `INOE`
  while the passport says `INOUE`: give that as the override. MOFA lets a passport carry another spelling (`ITOH`,
  `OHNO`) on request, so the policy is `AnyIcaoSpelling`; a reading that starts with a small kana, `ん` or `っ` has no
  spelling and needs the override too. The iteration marks `ゝゞヽヾ` repeat the kana before them (`いすゞ` is `ISUZU`). A
  name part or reading cannot start with `ー` or an iteration mark.
- **Scripts.** Name parts are checked with `\p{sc=Han}`, `\p{sc=Hangul}`, `\p{sc=Arabic}`, `\p{sc=Devanagari}` and the
  like, letters only (`\p{Lo}` for Han and Hangul: radicals, Hangzhou numerals and the Hangul tone marks are refused; CN
  and JP list `〇`, JP also `々` explicitly); the characters of a name that are Script=Common or Inherited are listed
  explicitly: `ー` U+30FC and `〆` U+3006 (JP), `·` U+00B7 (CN, KR; U+30FB and U+2022 are stored as U+00B7), the Arabic
  harakat U+064B-0652 and U+0670 (the tatweel U+0640 is removed; the Persian kaf U+06A9 and yeh U+06CC are stored as
  U+0643 and U+064A), the Devanagari ZWJ and ZWNJ right after a virama. Hanja, Uyghur, Tibetan and Mongolian script
  names are not covered.

### AU driving licences and the jurisdiction factory

```php
use MSSTC4PHP\ValueObject\Documents\Document\AU\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\AU\Jurisdiction;

$licence = DriverLicenceNumberFactory::fromNumberIn('01234567', Jurisdiction::NSW);   // NswDriverLicenceNumber
DriverLicenceNumberFactory::jurisdictionsMatching('01234567');                       // [ACT, NSW, NT, QLD, TAS, VIC]
DriverLicenceNumberFactory::tryFromNumberIn('01234567', Jurisdiction::WA);           // null (fromNumberIn() says why)
```

Every factory method `from…()` has a `try…()` twin (`tryFromNumberIn()`, `tryFromNumber()`, since 1.1) with the same
parameters: it returns null exactly where the `from…()` throws a value refusal (vo-base `InvalidValueObjectException`,
including `AmbiguousJurisdictionException` and "(format not covered)"), so it is null when no candidate or several fit.

**There is no AU `fromNumber()`:** with the formats published today every valid number fits two or more
jurisdictions, so a bare number never tells the issuer. Pass the issuer to `fromNumberIn()`; `jurisdictionsMatching()`
lists the candidates in enum order (see `docs/sources/AU.md`). The CA health card factory keeps `fromNumber()` (some
numbers fit one province only); it never picks one of several jurisdictions silently: it throws
`AmbiguousJurisdictionException`, which extends `InvalidValueObjectException` like every input error and whose
`jurisdictions()` tells which `fromNumberIn()` calls would work, or `InvalidDocumentNumberException` when none fits.

### US DL/ID numbers and the jurisdiction factory

```php
use MSSTC4PHP\ValueObject\Documents\Document\US\DlIdNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\US\Jurisdiction;

DlIdNumberFactory::fromNumberIn('123456789', Jurisdiction::NY);   // NyDlIdNumber
DlIdNumberFactory::jurisdictionsMatching('Y620-9305-7270');       // [IL], among the covered jurisdictions
DlIdNumberFactory::jurisdictionsMatching('123456789');            // 11 states
DlIdNumberFactory::fromNumberIn('123456789', Jurisdiction::CA);   // "Invalid US-CA DL/ID number (format not covered)."
DlIdNumberFactory::tryFromNumberIn('123456789', Jurisdiction::CA); // null
```

`DlIdNumberFactory` covers the 30 jurisdictions with a published format. The 21 others have no class, and
`fromNumberIn()` refuses them (`docs/sources/US.md` "DL/ID numbers"). `jurisdictionsMatching()` lists the covered
jurisdictions a number fits: some numbers fit exactly one of them (10 digits: IN; a letter and 13 digits: WI; WA's
`WDL` numbers), but an uncovered state could have issued the same number.

**No `fromNumber()` for a partly covered country** (US and CA licences, MX licences, BR identity cards): a single match
among the covered jurisdictions does not identify the issuer, and the result would change in a minor release that
covers one more jurisdiction. Pass the issuer to `fromNumberIn()`. `fromNumber()` may return, additively, once a country
is fully covered and some bare numbers fit one jurisdiction only; CA health cards are, and keep it (AU licences are
fully covered, but every valid number fits several jurisdictions, so AU has none).

### Canadian provincial documents

```php
use MSSTC4PHP\ValueObject\Documents\Document\CA\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCard\OnHealthCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\CA\HealthCardNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\CA\Jurisdiction;

DriverLicenceNumberFactory::fromNumberIn('890562', Jurisdiction::SK);   // SkDriverLicenceNumber, value 00890562
DriverLicenceNumberFactory::jurisdictionsMatching('L1531-171274-08'); // [QC], among the covered jurisdictions
DriverLicenceNumberFactory::jurisdictionsMatching('347631');          // [BC, SK]
DriverLicenceNumberFactory::fromNumberIn('123', Jurisdiction::MB);     // "Invalid CA-MB driver's licence number (format not covered)."

$card = OnHealthCardNumber::fromCard('9876543217 YM');                // OnHealthCard
$card->number();                                                       // OnHealthCardNumber, value 9876543217
$card->versionCode();                                                  // OnHealthCardVersionCode "YM" (null if none printed)
HealthCardNumberFactory::fromNumber('9876543217');                     // AmbiguousJurisdictionException: CA-NS, CA-ON
HealthCardNumberFactory::fromNumber('123456789');                      // AmbiguousJurisdictionException, 6 jurisdictions
HealthCardNumberFactory::tryFromNumber('123456789');                   // null (ambiguous); tryFromNumberIn() exists too
```

The licence factory works like the US one (6 of 13 jurisdictions covered, no `fromNumber()`); the health card factory
covers all 13 and keeps `fromNumber()`. When `HealthCardNumberFactory::fromNumber()` finds no match and the input
is an Ontario card with a version code, its message points to `OnHealthCardNumber::fromCard()` (nothing of the input is
echoed). BC and SK licence numbers share every shape (1-8 digits, stored padded to 8), so a bare BC or SK number is
always ambiguous: pass the issuer. All 13 health card formats are published (Ontario's reciprocal-billing table, checked
against each province's own text where one exists). The ON version code is part of the card, not of the health number:
two cards of one person have the same number and are equal, and they differ in the `OnHealthCardVersionCode`. Store the
two in their own columns. `OnHealthCardNumber::from()` and the factories refuse a number followed by its version code
instead of dropping the code: split the printed card with `fromCard()`.

### Mexican state driving licences

```php
use MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicenceNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\MX\Jurisdiction;

DriverLicenceNumberFactory::fromNumberIn('1234 5678 9012', Jurisdiction::MEX);   // MexDriverLicenceNumber, value 123456789012
DriverLicenceNumberFactory::jurisdictionsMatching('123456789012');              // [MEX], the only covered entity
DriverLicenceNumberFactory::fromNumberIn('123456789012', Jurisdiction::CMX);    // "Invalid MX-CMX driver's licence number (format not covered)."
DriverLicenceNumberFactory::tryFromNumberIn('123456789012', Jurisdiction::CMX); // null
```

Only the Estado de México publishes a number format (its validator's field rule: twelve digits). The other 31
entities have no class and `fromNumberIn()` refuses them. Any other state could issue a twelve-digit number, so
there is no `fromNumber()`: pass the issuer to `fromNumberIn()` (`docs/sources/MX.md` "State driving licences").

### Brazilian state identity cards (RG)

```php
use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCard\SpIdentityCardNumber;
use MSSTC4PHP\ValueObject\Documents\Document\BR\IdentityCardNumberFactory;
use MSSTC4PHP\ValueObject\Documents\Document\BR\Jurisdiction;

IdentityCardNumberFactory::fromNumberIn('12.345.678-2', Jurisdiction::SP);   // SpIdentityCardNumber, value 123456782
IdentityCardNumberFactory::jurisdictionsMatching('24.681.357-X');            // [SP], the only covered unit
IdentityCardNumberFactory::jurisdictionsMatching('12.345.678-3');            // [] (wrong SP check character)
IdentityCardNumberFactory::fromNumberIn('1234567', Jurisdiction::RJ);        // "Invalid BR-RJ identity card number (format not covered)."
IdentityCardNumberFactory::tryFromNumberIn('1234567', Jurisdiction::RJ);     // null
SpIdentityCardNumber::fromNumberAndCheckCharacter('1.234.567', null);         // card without a printed check character: value 012345672
```

Only São Paulo publishes an RG format (the SSP's antecedentes form: up to eight digits and a required check character
with its algorithm). The other 26 units have no class and `fromNumberIn()` refuses them. An RG of another state can fit the SP shape (its
last character may happen to be SP's check character), so there is no `fromNumber()`: pass the issuer to
`fromNumberIn()`. An old SP card that does not print its check character is still valid:
`fromNumberAndCheckCharacter()` takes the form's two fields and stores the computed character (`from()` and the factory
want both in one string). The RG of a CIN is its CPF: use `Document\BR\Cpf`. Old-model cards stay valid until
2032-03-01 (Decreto nº 10.977/2022, art. 25) (`docs/sources/BR.md` "RG per state").

## Personal names

A national name part (`Name\RU\FamilyName`, `GivenName`, `Patronymic`, and `FamilyName`, `GivenName` of `Name\AU`,
`Name\DE`, `Name\FR`, `Name\IT`, `Name\ES`, `Name\NL`, `Name\CH`, `Name\GB`, `Name\TR`, `Name\US`, `Name\CA`,
`Name\MX`, `Name\BR`, plus `Name\US\MiddleName`; the Asian name parts have the same rules in their own script) is
NFC-normalised, trimmed, has inner whitespace runs collapsed to one space, and
keeps its letter case. Limits: 100 characters per part. Russian parts accept only the 33 Russian letters; a Cyrillic
name typed with Latin look-alikes (Latin `a`, `o`, `e`, `c`) is refused. A compatibility character (its NFKC form
differs: fullwidth `Ｓｍｉｔｈ`, `ª`, the ligature `ﬁ`) is refused in every country, because it would be a second stored
spelling of the same name; the ones an ICAO table lists itself (`Ĳ`, `Ŀ`) are kept. Messages say "(empty)" for
an empty part, "(too long)" for one over 100 characters, "(compatibility character)", or "(invalid character)" for a
letter or separator that is not allowed where it stands.

```php
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName as IcaoPersonName;
use MSSTC4PHP\ValueObject\Documents\Name\RU\{FamilyName, GivenName, Patronymic, PersonName};

$name = new PersonName(FamilyName::from('Иванов'), GivenName::from('Иван'), Patronymic::from('Иванович'));
$name->full();                  // "Иванов Иван Иванович"
$name->short();                 // "Иванов И. И."
(string) $name->latin();        // "IVANOV<<IVAN"
$name->latin()->mrzField();     // "IVANOV<<IVAN" padded with "<" to 39 characters
```

### ICAO Latin name

`Icao\PersonName` is the canonical Latin form of ICAO Doc 9303 Part 3 §4.6: characters `A-Z`, `-` and `<`; `<<`
separates the primary identifier from the secondary ones and a single `<` stands for a space; maximum 255 characters.
Hyphens are kept (`RIMSKY-KORSAKOV`) and become `<` only in `mrzField()`.

```php
use MSSTC4PHP\ValueObject\Documents\Icao\PersonName;

$latin = PersonName::fromParts('van der Berg', ['Jan', 'Pieter']);
(string) $latin;             // "VAN<DER<BERG<<JAN<PIETER"
$latin->visualZone();        // "VAN DER BERG, JAN PIETER"
$latin->mrzField(39);        // padded with "<" or truncated per Doc 9303 Part 4 §4.2.3
```

### ICAO machine readable zone

`Icao\Mrz\MachineReadableZone::fromString()` reads the MRZ of a TD3 passport (2 lines of 44), a TD2 (2 of 36) or a
TD1 identity card (3 of 30) and returns a `Td3`, `Td2` or `Td1`. Lines are joined by `\n` or `\r\n`, or the whole zone
is one string of 88, 72 or 90 characters; the stored form joins them by `\n`. Every check digit is verified (the
composite one included), TD1 and TD2 long document numbers are joined from the optional data.

| Class | Document | Check | Source |
|---|---|---|---|
| `Icao\Mrz\Td3` | MRP and other TD3 documents | document number, birth date, expiry date, personal number, composite | Doc 9303 Parts 3, 4 |
| `Icao\Mrz\Td2` | TD2 official travel documents | document number (long form too), dates, composite | Doc 9303 Parts 3, 6 |
| `Icao\Mrz\Td1` | TD1 official travel documents | document number (long form too), dates, composite | Doc 9303 Parts 3, 5 |

```php
use MSSTC4PHP\ValueObject\Documents\Icao\Mrz\MachineReadableZone;

$mrz = MachineReadableZone::fromString($twoLinesOf44);
$mrz->issuingState();                       // "UTO", raw (also "D", "GBD", "XXA")
$mrz->issuingCountry();                     // CountryCode or null outside ISO 3166
$mrz->name();                               // Icao\PersonName
$mrz->documentNumber();                     // "L898902C3"
$mrz->birthDate(new DateTimeImmutable());   // null when the day or month is unknown (<<)
$mrz->masked();                             // document number, optional data, birth date -> "*"
```

`MachineReadableZone` implements `HasSex` but not `HasBirthDate`. `sex()` is `Sex::Female`, `Sex::Male`, or `Sex::Unspecified`
for the filler `<` (Doc 9303 Parts 4-6: "< = unspecified"); the VIZ letter `X` is not an MRZ value and is refused.
`HasBirthDate::birthDate()` takes no argument, but the MRZ holds only two year digits and Doc 9303 gives no century, so
`birthDate($reference)` and `expiryDate($reference)` take the date they resolve against (no hidden clock). `masked()` puts `*`
over the document number, optional data, birth date and their check digits; `*` is not an MRZ character, so a masked zone
never parses. `birthDate($reference)` takes the most recent past century; `expiryDate($reference)` the century that puts the
year between 84 years before and 15 years after the reference year (documents are valid for about 10 to 15 years, so an
archived document reads as past: `760101` with a 2026 reference is 1976). These windows are part of the 1.x contract
and change only in a major release; the parameter was `$today` for `birthDate()` before 1.0 (named arguments). `birthDateDigits()` and `expiryDateDigits()`
give the raw `YYMMDD`. `Td3::fromString()` (and `Td1`, `Td2`) refuses the lines of another format as "Invalid ICAO MRZ."
without applying that format's rules. A serialized MRZ holds its value alone and is validated again on `unserialize()`. The visa formats MRV-A and MRV-B (Part 7) are not read.
`MachineReadableZone` is sealed: type-hint it, do not extend it (its `layout()` hook is `@internal`; the package may add
the visa formats as further subclasses). Notes: `docs/sources/ICAO.md` section 5.

`Icao\Transliterator` turns a national name part into its ICAO spelling (`toLatin()`, or `variants()` for every
spelling the table allows). Two rules that follow the sources, not the obvious guess:

- The Russian derived Latin name **omits the patronymic** (`IVANOV<<IVAN`): MVD Orders 996 and 186 do not write it in
  Latin in the passport.
- The soft sign **Ь is omitted** (`Игорь` is `IGOR`): the ICAO table 6.B has no row for it, the MVD table has none
  either. The hard sign Ъ is `IE`.
- A Latin letter **missing from table 6.A is spelled as its base letter** (`Nguyễn` is `NGUYEN`, Romanian `Ș`/`Ț` are
  `S`/`T`): the package's fallback, not an ICAO rule (`docs/sources/ICAO.md`). A letter with no base in the table
  (`ƒ`) is still refused.
- Transliteration tables and override rules change **only in a major version**: a stored override is checked again
  when it is read, so a narrower rule would make stored names unreadable.

### Canonical form and the override rule

A person's Latin name is derived from the national one. A different official spelling (for example one printed in an
existing passport) can be given as an override. `Icao\PersonName::from()` normalises it first: letter case, spaces
next to a `<` (`IVANOV << IVAN`) and the trailing `<` padding of an MRZ field (`IVANOV<<IVAN<<<<`). What is accepted
then depends on the country's sources:

| Country | Accepted override | Why |
|---|---|---|
| RU | any valid ICAO name (`IVANOVA<<YULIYA` for Иванова Юлия, where the table gives `IVANOVA<<IULIIA`) | MVD Orders 996/186 (§32, §140) write the name "так, как это указано в подтверждающем документе" |
| AU, DE, FR, IT, ES, NL, CH, TR, US, MX | only an ICAO spelling of the name (`MULLER<<ZOE` for Müller Zoë); `<` for a hyphen is stored as the hyphen (policy `TransliterationVariant`) | Latin-script names: no source lets a passport keep another spelling, so the table spellings are the only ones |
| GB | any valid ICAO name (policy `AnyIcaoSpelling`) | HM Passport Office lets a passport carry an evidenced spelling (`docs/sources/GB.md`) |
| CA | any valid ICAO name (policy `AnyIcaoSpelling`) | the Passport Program points to "alternative spellings" and lets an adult drop or invert given names (`docs/sources/CA.md`) |
| BR | any valid ICAO name (policy `AnyIcaoSpelling`; `SANTOS<OLIVEIRA<<ANA` for `dos Santos de Oliveira, Ana`) | the Polícia Federal's passport system abbreviates a field over 30 characters (drops `de`, `da`, `dos`, then shortens words to one letter), and "a abreviação não configura erro no passaporte" (`docs/sources/BR.md`) |
| JP | any valid ICAO name (policy `AnyIcaoSpelling`; `INOUE<<TARO` for 井上 いのうえ, derived `INOE<<TARO`) | MOFA writes a non-Hepburn spelling (`ITOH`, `OHNO`) on request (`docs/sources/JP.md` §4) |
| IN (Latin letters) | only an ICAO spelling of the parts (policy `TransliterationVariant`) | no MEA text read lets a passport keep another spelling (`docs/sources/IN.md`) |
| CN, KR, ID, SA, IN (Devanagari) | none: the Latin form is **required** and never derived, so there is nothing to override | the passport spelling is a personal choice (pinyin against Cantonese, Kim against Gim, Arabic transliteration); it is checked only as a valid `Icao\PersonName` |

An override that is the derived form (also when it writes a hyphen as `<`) is not kept, so `latinOverride()` is null
and the name equals the one built without it. Text that is no ICAO name (`IVANOV1<<IVAN`) is refused by
`Icao\PersonName::from()` in every country.

```php
$name = new PersonName(
    FamilyName::from('Иванова'),
    GivenName::from('Юлия'),
    null,
    IcaoPersonName::from('Ivanova << Yuliya'),   // normalised to IVANOVA<<YULIYA
);
$name->latin();           // IVANOVA<<YULIYA
$name->latinOverride();   // the same; null when the Latin form is derived
```

The storage payload of a name that derives its Latin form stores only the override (`latinOverride: null`
otherwise), never the derived form; payloads written by 0.x under the key `latin` still load. The JSON view
(`jsonSerialize()`) of every name has the full Latin form under `latin`, and reads back through the codec as the same
name.

## Bridges

The value objects are plain `vo-base` value objects, so every
[`vo-base-*`](https://github.com/msstc4php/vo-base#framework-integration) bridge handles them:

| Need | Where |
|---|---|
| Validation, request input, route parameters | As is in every vo-base bridge (Symfony bundle, Laravel, Yii 2, CodeIgniter 4, Spiral) |
| Storage of document numbers and name parts | As is in Laravel (`AsValueObject`), Yii 2 (behavior), CodeIgniter 4 (casts) and Cycle (typecast) |
| Any MRZ format in one column or parameter | Type it `Icao\Mrz\MachineReadableZone`: the fitting `Td1`, `Td2` or `Td3` comes back (vo-base 1.4) |
| Composite names | Register `Codecs` once per application (next section) |
| Doctrine ORM / MongoDB ODM | [`msstc4php/vo-documents-doctrine`](https://github.com/msstc4php/vo-documents-doctrine): one type per class, named `vod_<cc>_<snake_name>` |

Typed properties, route parameters and casts name a concrete class or `MachineReadableZone`, never an interface such
as `Document\AU\DriverLicenceNumber`: the mapper cannot build an interface (which state's number would it be?), so it
reports it as unsupported, a configuration error rather than a 500 on request input. The interface is the type of
`DriverLicenceNumberFactory` results.

### Composite names need the codecs

Document numbers and name parts are strings and need nothing. The composite names (`Name\<CC>\PersonName` of every
country: RU, AU, DE, FR, IT, ES, NL, CH, GB, TR, US, CA, MX, BR, CN, JP, KR, IN, ID, SA, and the Devanagari one of IN)
are read through a payload codec. `Codecs` implements vo-base's `Payload\ProvidesCodecs`; give the bridge's mapper its
codecs once:

```php
use MSSTC4PHP\ValueObject\Documents\Codecs;
use MSSTC4PHP\ValueObject\Payload\ValueObjectMapper;

// Laravel (vo-base-laravel 0.3+): casts, rules, request input and route parameters all read the binding.
$this->app->singleton(ValueObjectMapper::class, static fn () => new ValueObjectMapper(Codecs::all()));

// Yii 2 (vo-base-yii2 0.3+): the validator, the behavior, model values and action parameters.
Yii::$container->setSingleton(ValueObjectMapper::class, static fn () => new ValueObjectMapper(Codecs::all()));

// CodeIgniter 4 (vo-base-codeigniter4 0.3+), in app/Config/Services.php:
public static function valueObjectMapper(bool $getShared = true): ValueObjectMapper
{
    return $getShared ? static::getSharedInstance('valueObjectMapper') : new ValueObjectMapper(Codecs::all());
}

// Spiral (a Bootloader listed after ValueObjectBootloader; the Cycle typecast reads the same container)
public function defineSingletons(): array
{
    return [ValueObjectMapper::class => static fn () => new ValueObjectMapper(Codecs::all())];
}

// Cycle without a container: one typecast per role, with your mapper
S::TYPECAST_HANDLER => [new ValueObjectTypecast('account', new ValueObjectMapper(Codecs::all())), Typecast::class],
```

```yaml
# Symfony (vo-base-bundle 2.1+)
msstc4php_vo_base:
    codec_providers: ['MSSTC4PHP\ValueObject\Documents\Codecs']
```

Older bridge releases read the container in fewer places (Laravel casts and rules, the Yii 2 behavior) and
CodeIgniter 4 before 0.3 had no way to add codecs; pass the mapper to those explicitly or upgrade the bridge.

## Not covered

Collected from the source notes; each is a deliberate omission with its reason in `docs/sources/`.

- **DE:** Führerschein number (FeV composition without length or check digit); provisional passports and identity cards
  (`docs/sources/DE.md`).
- **FR:** the identity card issued since August 2021 (no official format); the pre-1975 driving licence form
  (`docs/sources/FR.md`).
- **IT:** carta d'identità elettronica number and patente number (no reachable official format); the eleven-digit
  provisional codice fiscale (`docs/sources/IT.md`).
- **ES:** passport number (the official register could not be read); the driving licence number is the DNI or NIE and
  has no class of its own; the NIF of legal persons and minors (`K`, `L`, `M`) (`docs/sources/ES.md`).
- **NL:** rijbewijsnummer (no published format) (`docs/sources/NL.md`).
- **CH:** a per-canton licence format (the licence is federal); the Allianzname as a separate field
  (`docs/sources/CH.md`).
- **GB:** the HMRC Temporary Reference Number, the passport thin-film serial number, the licence issue number
  (`docs/sources/GB.md`).
- **TR:** sürücü belgesi number (a running sequence per province, no format), the identity card serial number
  (`docs/sources/TR.md`).
- **US:** a middle name or suffix beside a one-word name (no source defines it), the IRM's open list of "never issued"
  SSN examples (not refused), ATIN and IRSN; the DL/ID numbers of the 21 jurisdictions AK, AR, CA, DC, DE, GA, LA, MN,
  MO, MT, NC, NH, NV, OH, OR, SC, SD, TN, UT, VA and WV (no official format published), the ID cards of CT, HI, IN, KY,
  ME, NJ, NM, OK and RI (numbering not stated), the Maryland customer ID, and the decoding of name or birth data in IL,
  WA, FL and MI numbers (`docs/sources/US.md`).
- **CA:** refusing a SIN that fails its check digit (reported by `hasValidCheckDigit()`; the CRA says to report such a
  SIN as given), the province meaning of a SIN's first digit, the CRA individual and temporary tax numbers, the passport print form of a one-word
  name, special, diplomatic, temporary and emergency passport numbers (only the general format is published).
  Provincial documents: the driver's licence numbers of the 7 jurisdictions MB, NB, NL, NT, NU, PE and YT (their
  authorities name the number without a format; `fromNumberIn()` throws "(format not covered)"); the photo ID card
  numbers of AB, BC (BCID), ON (Photo Card) and QC; the encoded data of the ON and QC licence numbers (not defined in
  text) and the NS birth date (two-digit year); SK validation numbers, ICBC client numbers and the ON Driver
  Identification Number; the Manitoba six-character family registration number; prior health number formats (AB 11, MB
  6, NS 11 numerics); the QC temporary NAM; the QC "chiffre valideur" and every health check digit other than BC and ON
  (no algorithm published); whether an ON version code is the current card's (`docs/sources/CA.md`).
- **MX:** the passport number (the 2021 e-passport agreement gives only the booklet number `N` + 8 digits; older
  passports have no published format), the RFC of a legal person, the CURP, RFC, NSS and elector-key check characters
  (no algorithm published), the RENAPO state catalogue and "palabras inconvenientes" list (not published with the
  Reglas: any two letters are accepted, substituted forms too), the INE sex and state code lists of the elector key,
  the fields of the NSS, other INE card numbers (CIC, OCR). State driving licences of 31 of 32 entities (all but
  MX-MEX): the state laws (NL, JAL, TAB, GUA, CDMX) name the number without a format; the issuer's pages of BCN, CMX,
  MIC, NLE, PUE and YUC could not be read on 2026-11-16 UTC and are to be re-checked (`docs/sources/MX.md`).
- **BR:** the check digits of the título de eleitor and the CNH registration number (the TSE and CONTRAN name "módulo
  11" without the weights, and no government text found gives them), the CNH número do espelho and formulário Renach,
  passport formats other than the Polícia Federal's two letters and six digits, the CGU masking of the CPF
  (`***.012.389-**`, LDO 2011: it shows more digits than `masked()`, so it waits for 2.0), a person with no surname (the CNJ code requires one). The RG of 26 of 27 units (all but BR-SP): their
  online forms limit the field at most (BA: eight digits with an optional control digit and no algorithm); the forms of
  BA, CE, MG, PE, RJ, RN and RS could not be read live on 2026-11-16 UTC and are to be re-checked
  (`docs/sources/BR.md`).
- **CN:** the 港澳居民来往内地通行证 (回乡证) and 台湾居民来往大陆通行证 (台胞证) numbers (formats are published but were outside the
  plan; the `HA`/`MA` shape beyond nine characters is not), the first-generation 15-digit citizen number (invalid since
  2013-01-01, refused by length), official (公务), diplomatic (外交) and public-affairs (因公) passports, Hong Kong and
  Macao SAR passports and every series other than the ordinary `E`/`G` (no published format), a check digit of the
  15-character permanent residence number, the **driving licence** (its number is the citizen identity number: use
  `CitizenIdentityNumber`), hukou, the social credit code, the military ID, and Uyghur, Tibetan and Mongolian script
  names (`docs/sources/CN.md`).
- **JP:** the 個人番号カード number, the 特別永住者証明書 number (no published format read), the old 外国人登録証明書 number,
  the corporate number (法人番号), and the derivation of a long vowel across a morpheme boundary (井上 is derived `INOE`;
  give the override `INOUE`) (`docs/sources/JP.md`).
- **KR:** Hanja names, passports other than the multiple-entry `M` series (single-use and emergency 단수·긴급, official,
  diplomatic and others: no published prefix), the 영주증 and 국내거소신고증 numbers as such, and the 법인등록번호 and
  사업자등록번호 (`docs/sources/KR.md`).
- **IN:** the EPIC (voter ID: the ECI text could not be read, legacy series exist), the driving licence number (no
  official text states a structure; the 2+2+4+7 layout is on aggregators only), the Passport Seva file number, the ABHA
  address, the Aadhaar Virtual ID (refused by length), voter-roll serial numbers, the ration card and state-issued
  documents (`docs/sources/IN.md`).
- **ID:** the SIM (driving licence) number (the NIK form is covered by `NationalIdentityNumber`, an old SIM number has
  no quotable rule), the passport number (no Imigrasi text with the layout found), the BPJS Kesehatan number (regional
  articles and secondary sites only), and the Kemendagri code list behind the NIK region code (`docs/sources/ID.md`).
- **SA:** the border number (رقم الحدود: blogs only), the passport number (no Jawazat format found), a check digit of
  the national ID and iqama number (none is published) and the **driving licence** (it carries the identity number:
  use `NationalIdentityNumber` or `IqamaNumber`) (`docs/sources/SA.md`).
- **No region value object for RU**: no field encodes a region with a published mapping to ISO 3166-2:RU (`RU.md`).
- **RU driving licences of the 1999 form** (Cyrillic series): expired by 2026 (`RU.md`).
- **RU old-form OMS policies** (series and number) and AU **11-digit Medicare individual numbers**.
- **AU driving licence card numbers**: a different number on the same licence, left for a later release (`AU.md`).
- **AU single names (mononyms)**: the constructor requires a given name (`AU.md`).

## Rules that may relax

Every refusal rests on an official text. These rest on a thin one, or decode a value from a secondary one; a minor
release may relax them (accept more), never tighten them:

- **IN Aadhaar Verhoeff check:** UIDAI's 2010 paper "recommends" the scheme; enforced, as UIDAI is the issuer
  (`docs/sources/IN.md`).
- **MX CURP:** `A-J` at position 17 and no `Ñ` at 1-4 follow RENAPO's Instructivo and Reglas; they are listed because
  RENAPO may issue more letters once `A-J` run out (`docs/sources/MX.md`).

Relaxed in 1.0 (no official text supported the refusal): the SA first digit (it only routes now), the calendar check
of the ID NIK, the KR foreigner registration number, the MX clave de elector and the GB DVLA driver number (only the
month and day ranges remain, see "Birth dates" below), `Ñ` in the MX clave de elector, and the century of the KR
foreigner registration number (no `HasBirthDate`).

## Limits to know about

- **Asia, checksums:** verified are the CN citizen identity number and 2023 permanent residence number (ISO 7064 MOD
  11-2, GB 11643), the JP My Number (総務省令) and the IN Aadhaar (Verhoeff). Format only: every other number of the six
  countries, among them the JP driving licence check digit ("モジュラス11" without weights), the KR registration numbers
  (random digits since 2020-10), the IN PAN check letter and the ID and SA numbers.
- **Aadhaar rests on a thin text:** UIDAI's 2010 paper only *recommends* Verhoeff (`docs/sources/IN.md`); it is
  enforced, as UIDAI is the issuer. The Saudi first-digit rules 1 (national ID) and 2 (iqama) come from a ZATCA staff
  announcement on its developer forum, where they are warnings (`docs/sources/SA.md`); since 1.0 they only route a
  number to its class and refuse nothing. **Both SA classes accept the same ten digits**, so trying one class first
  (`NationalIdentityNumber::tryFrom($n) ?? IqamaNumber::from($n)`) cannot tell them apart: route with
  `IdentityNumberFactory::fromNumber()` (`1` national ID, `2` iqama, since 1.1), which refuses another first digit, and
  decide what such a number means in your context (both classes still accept it).
- **Asia, derived Latin names:** only JP derives one (MOFA Hepburn); `井上` (いのうえ) is derived `INOE` and needs the
  override `INOUE`. The other five countries require the Latin form of the passport.
- **Birth dates:** a birth date in a number is checked against the calendar only where an official text makes the
  digits the holder's birth date and binds the issuer to construct them from it: the CN citizen and 2023 permanent
  residence numbers (GB 11643 §5.1.2, "按GB/T 7408的规定执行"), the MX CURP (RENAPO Instructivo), the KR resident
  registration number (주민등록법 시행규칙 Art. 2 and the 행정안전부 사무편람), the IT codice fiscale (DM 23 dicembre 1976), the GB
  CHI number (NHS Data Dictionary, and PHS `chi_check()` refuses an invalid date) and the ICAO MRZ (Doc 9303 Part 3
  §4.8). Where the year has no century, 29 February is accepted for a two-digit year divisible by four. Everywhere
  else only the month 01-12 and the day 01-31 are checked (30 February and 31 April are accepted): the ID NIK, the KR
  foreigner registration number, the MX clave de elector and RFC, the GB DVLA driver number and the CA NS licence and
  QC health card numbers, whose texts describe the digits but do not say a number with an impossible date is never
  issued. The DE pension insurance number checks no date at all (VKVV § 2 field with unpublished rules for an
  unproven birth date; `birthDateField()` returns it as issued). The decision per class is in its source note.
- **Format only, check digit not verified:** INN, TFN, Medicare card number, the CA health card numbers other than BC
  and ON, the MX CURP, RFC, NSS and clave de elector, and the BR título de eleitor and CNH registration number. No
  official algorithm is published (FNS says only "по алгоритму, определенному ФНС"; the ATO gives the TFN algorithm on
  request under a confidentiality agreement; the ADHA appendix is not reachable; the TSE and CONTRAN name "módulo 11"
  without weights), so a mistyped check digit is accepted.
- **Check digit reported, not enforced (since 1.2):** the DE Steuer-ID (ELSTER), the CA SIN (CRA) and the GB CHI
  number (PHS) have `hasValidCheckDigit()`. `from()` accepts a number that fails, as 1.0 did; refusing waits for 2.0. Only
  checksums quoted from an official source are verified: SNILS, OMS policy, DE pension insurance number, IT codice
  fiscale, ES DNI and NIE, NL BSN, CH AHV number, GB NHS and H&C numbers, BC and ON health card numbers, BR CPF, NIS,
  CNS and the SP RG. Any government body's own publication counts as official (the ANS algorithms for the NIS and CNS);
  another body's intake policy does not (repeated-digit CPFs are accepted).
- **MX clave de elector letters** (positions 1-6) are `A-Z` or `Ñ` since 1.0: INE says only "primeras letras" and,
  unlike RENAPO for the CURP, substitutes nothing, so a key with `Ñ` is accepted (`docs/sources/MX.md`). The key and
  the RFC upper-case ASCII letters and `ñ` only; `ß`, `ﬁ`, `ı` and every other character outside the official
  alphabet are refused, never rewritten.
- **Americas, shapes that look alike:** a bare BC or SK licence number is always ambiguous in the factory (1-8 digits,
  stored zero-padded to 8: `347631` and `00347631` are one number); a twelve-digit MX licence of another state and an
  RG of another state are reported as MX-MEX and BR-SP, because only those formats are published; US `123456789` fits
  11 states. Pass the issuer to `fromNumberIn()`. A jurisdiction without a published format throws
  "(format not covered)".
- **ICAO punctuation (MX):** the Mexican name parts accept `/` and `.` (`D/AMICO`, `MA. GUADALUPE`) as RENAPO records
  them, and the ICAO form omits them (`DAMICO`, `MA<GUADALUPE`), as Doc 9303 Part 3 §4.6 prescribes; no other country
  accepts them. A Mexican name with no surname has the given names as its primary identifier.
- **Unverified spots in the American sources:** the US SSN accepts the IRM's "never issued" examples, a US passport book accepts any letter (a card number parses as a book number), and the
  hosts listed as "re-check" in "Not covered" could not be read on 2026-11-16 UTC.
- **Not verified against the official PDF:** the 16-digit length of the OMS policy and the FFOMS Order 79 text it is
  checked against (www.ffoms.gov.ru is unreachable from here, the text is quoted from a copy), and the 2 + 7 split of
  the international passport number (no text read states it; `RU.md`).
- **Format only in Europe** (see the table above): DE Steuer-ID (the check digit is reported; the ELSTER structure
  rules, no leading zero and one repeated digit, are not applied: an ELSTER example number breaks one of them), FR NIR
  key and numéro fiscal, the identity card, passport and licence numbers of every country, ES social security number,
  TR T.C. Kimlik No (NVİ shares the algorithm only with public bodies; the popular formula fails the official PRADO
  specimens) and the CHI check digit (reported, not enforced). ES pre-2008 NIE letters are accepted as any `A-Z`.
- **Encoded data only where the source defines it:** the DE pension insurance number exposes strings (no century, no
  published rule for unproven birth dates); Italian codice fiscale accepts any omocodia substitution over the seven
  numeric positions and `29` February when the two-digit year is divisible by four (no century is encoded).
- **Name limits by country:** FR names use the 2014 civil-status circular's letter list (`ñ`, `ł`, `ß` refused); NL
  names use the BRP Teletex letters and refuse the `ĳ` ligature (written `ij`); TR names refuse `Q`, `W`, `X` (Law 1353
  art. 3), hyphens and apostrophes; derived `Ö`/`Ü` stay `OE`/`UE` (table 6.A); ES accepts the Catalan middle dot
  only in `l·l` (omitted from the ICAO form, Doc 9303 Part 3 §4.6: `Marcel·lí` → `MARCELLI`; `ŀ` is stored as `l·`);
  every country with an apostrophe separator stores U+2019 and U+02BC as `'`;
  IT refuses the comma between given names; US parts store the Hawaiian ʻokina (U+02BB, U+2018) as an apostrophe, as the
  passport writes it; GB licence numbers of a holder with no first names would be refused (no published rule for them).
- **Source access:** FR passport and licence formats are France Titres-ANTS answers; CH passport length comes from
  the Basel-Stadt passport office and fedpol; TR passport format from the PRADO register; the ES NIF/NIE algorithm and
  several GB texts are cited through unmodified Internet Archive copies of the official page (original URL, archive URL
  and capture date in the source notes).
- Name parts are limited to 100 characters, `Icao\PersonName` to 255 (a very long real name is refused). Three long
  parts can exceed 255 in Latin (an ES name of three 100-character parts): the name is refused with
  "(Latin form too long)", unless the country accepts any ICAO spelling (RU, GB, CA) and an override is given.
- **RU, GB and CA accept an override without a derived form**: when a letter has no ICAO spelling or the derived form
  is too long, the override is kept as given (the passport authority writes a documented spelling); without an
  override the name is refused. An `AnyIcaoSpelling` override is stored as typed: `SMYTH<JONES<<MARY` and
  `SMYTH-JONES<<MARY` are two values. GB drops the `XXX` passport forename of a person with one name (`SMITH<<XXX` is
  `SMITH`); CA does not (no Passport Program text describes a placeholder).
- **The accepted name characters depend on the platform's PCRE:** the script classes (`\p{sc=Han}`, `\p{sc=Latin}`, …)
  follow the Unicode version of the installed PCRE (`lib-pcre >= 10.40`), so a newly assigned code point is accepted
  once PCRE knows it, and refused on an older build.
- **The transliteration variant cap (4096) is per ICAO word**, so a long list of given names with umlauts is accepted
  with an override. A single word with more than twelve two-way letters (`Ä` = `AE`/`A`) or more than seven three-way
  letters (`Ü` = `UE`/`UXX`/`U`) is still refused when an override that differs from the derived form is given.

## Backward compatibility

From 1.0.0 on, a breaking change needs 2.0. The 1.x promise covers:

- public classes, methods and their parameter names (named arguments), and return types;
- the canonical (stored) string of every document number (also its `__toString()` and JSON value), the JSON view
  keys of the names (`latin` is the full Latin form everywhere) and the codec and `__serialize()` payload keys;
- enum backed values and case names (`Jurisdiction::NSW`, `NameSuffix::JR`);
- the public `MAX_LENGTH` of every string value object (in characters) and, in `vo-documents-doctrine`, the type
  names and column lengths;
- exception classes and their parents;
- the MRZ century windows of `birthDate()` and `expiryDate()`.

Rules for minor releases:

- **A minor never refuses a value an earlier 1.x release accepted.** A check that gets an official source later is
  reported only, by a new `hasValid…(): bool` method; refusing waits for 2.0. See "Rules that may relax" for the
  refusals that rest on a thin text.
- A minor may accept more (relax a rule, cover a new jurisdiction or document), but only values that fit the frozen
  `MAX_LENGTH`, and only when that adds no candidate to any factory's frozen candidate set: a relaxation that would
  make a member of a `fromNumber()` factory match a number it did not match before (so that a number that resolved to
  one class becomes ambiguous) waits for a major.
- **Factory candidate sets are frozen per major:** a newly covered jurisdiction gets its class in a minor and joins a
  factory's `fromNumber()` in the next major, so a number that resolves to one class in 1.x keeps resolving to it.
  `fromNumber()` exists only where a bare number can identify one jurisdiction of a fully covered country (CA health
  cards; AU licences are fully covered, but every number fits several jurisdictions); it may be added, additively,
  once that holds for another country. `SA\IdentityNumberFactory::fromNumber()` routes by the first digit, not by
  jurisdiction; its candidates (`NationalIdentityNumber` for `1`, `IqamaNumber` for `2`) are frozen for 1.x.
- Enum cases may be added in a minor (a new subdivision, a new `Sex`); give every `match` over a package enum a
  default arm.
- The package's interfaces (`Name\NationalPersonName`, the per-country document interfaces, `Contract\*`) are
  implemented by this package only; methods may be added to them in minor releases. Implementing them, or extending
  an `@internal` base, outside the package is outside the promise.
- `masked()` may hide more in a minor, never less.
- Payloads and serialized names written by 0.x keep loading through 1.x (`latin` as the override key of the names
  that derive their Latin form, `first`/`second` as the ES and MX surnames, the 0.2 PHP object layout); the 0.2 layout
  is read until 2.0. The change is one-way: 0.x cannot read what 1.x writes, so going back to 0.x after 1.x has written
  payloads is not supported, and stored 0.x text stays until it is re-encoded (CHANGELOG, "Upgrading from 0.x").

Not covered: the wording of exception messages (a message never contains the input, its length or any part of it);
everything in `Internal\` or marked `@internal`; the set of characters a name accepts follows the PCRE Unicode
version of the platform (`\p{sc=…}`): a code point assigned in a newer Unicode version is accepted where PCRE knows
it.

## Development

`make check` (php -l, PHPStan max, cs-fixer, Rector, deptrac), `make test`, `make infection`. See `CLAUDE.md`.
