# Indonesia — sources

All dates UTC. Sources read 2026-11-17.

| Source | Publisher | Where read |
|---|---|---|
| Peraturan Menteri Dalam Negeri No. 73 Tahun 2022 tentang Pencatatan Nama pada Dokumen Kependudukan (signed 2022-04-11, promulgated 2022-04-21, Berita Negara 2022 No. 429) | Kemendagri | text on <https://pasal.id/peraturan/permen/permendagri-no-73-tahun-2022> (an aggregator: "quoted from"); the official gazette PDF <https://peraturan.go.id/files/bn429-2022.pdf> closes the socket for WebFetch and does not connect for `curl` |
| "Yuk Simak, Ini Perbedaan NIK dan NIT" (blog) | Ditjen Dukcapil, Kemendagri | <https://dukcapil.kemendagri.go.id/blog/read/yuk-simak-ini-perbedaan-nik-dan-nit> |
| Perda Kabupaten Wonosobo No. 6 Tahun 2009 (Penyelenggaraan Administrasi Kependudukan), Pasal 22(1) | Pemkab Wonosobo | text on <https://pasal.id/peraturan/perda/perda-kab-wonosobo-6-2009> ("quoted from"); a local rule restating PP 37/2007 Art. 37, used only as a cross-check |
| "Wajib Tahu! Nomor KK Harus Diganti Ketika Kepala Keluarga Meninggal Dunia" (blog) | Ditjen Dukcapil, Kemendagri | <https://dukcapil.kemendagri.go.id/blog/read/wajib-tahu-nomor-kk-harus-diganti-ketika-kepala-keluarga-meninggal-dunia> |
| "Arti 16 Digit Nomor Kartu Keluarga" | Disdukcapil Kabupaten Klaten | <https://dukcapil.klaten.go.id/arti-16-digit-nomor-kartu-keluarga> (cites no law) |
| Siaran Pers SP-43/2022 "Simak Format NPWP Baru Berikut Ini!" (2022-07-20) | DJP, Kemenkeu | <https://www.pajak.go.id/sites/default/files/2022-07/SP-43%202022%20NPWP%20Format%20Baru%20PMK%20112%202022.pdf> (`pdftotext`) |
| Pengumuman PENG-6/PJ.09/2024 "Penggunaan NPWP pada Sistem Administrasi Perpajakan" (2024-02-13) | DJP, Kemenkeu | <https://www.pajak.go.id/sites/default/files/2024-02/PENG-6-2024%20PENGGUNAAN%20NOMOR%20POKOK%20WAJIB%20PAJAK%20PADA%20SISTEM%20ADMINISTRASI%20PERPAJAKAN.pdf> (`pdftotext`) |
| "Per Bulan Juli 2024, Korlantas Polri Sudah Menerapkan Pembaruan SIM Pakai NIK" | Polri (Tribrata News) | <https://tribratanews.polri.go.id/blog/nasional-3/per-bulan-juli-2024-korlantas-polri-sudah-menerapkan-pembaruan-sim-pakai-nik-77657> |
| Peraturan Polri No. 5 Tahun 2021 tentang Penerbitan dan Penandaan SIM | Polri | <https://peraturan.bpk.go.id/Download/210908/Perpol%20Nomor%205%20Tahun%202021.pdf> (`pdftotext`) |
| Peraturan Kapolri No. 9 Tahun 2012 tentang SIM (revoked by Perpol 5/2021), Pasal 41 | Polri | text on <https://pasal.id/peraturan/perban/peraturan-polri-no-9-tahun-2012> ("quoted from") |
| "Paspor Umroh Boleh 2 Suku Kata: Apa Artinya?" | Kantor Imigrasi Yogyakarta | <https://jogja.imigrasi.go.id/paspor-umroh-boleh-2-suku-kata-apa-artinya/> |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6 | ICAO | see `ICAO.md` |

Reachability: `peraturan.bpk.go.id`, `jdih.kemendagri.go.id` and `peraturan.go.id` are refused by the sandbox for `curl`;
WebFetch answers "Socket is closed" for `peraturan.go.id/files/*.pdf`, `disdukcapil.pasamanbaratkab.go.id` and
`sanggau.imigrasi.go.id` (PERDIM 11/2020), 403 for `peraturan.bpk.go.id/Details/*`, `pid.kepri.polri.go.id` (Perkap 9/2012
PDF), a certificate error for `korlantas.polri.go.id` and `web.bpjs-kesehatan.go.id`, and "unable to fetch" for
`web.archive.org`; `kupang.imigrasi.go.id` does not resolve. Secondary only (never a rule, never cited in code): news
(Kompas, detik, Bisnis, hukumonline, NU Online), `pasal.id` as the only readable copy of Permendagri 73/2022, python-stdnum
and Perl `Business::ID::SIM` (which describe a 12-digit SIM number).

## Names

**Permendagri 73/2022** (quoted from pasal.id): Art. 4(2) "jumlah kata paling sedikit 2 (dua) kata", "jumlah huruf paling
banyak 60 (enam puluh) huruf termasuk spasi", "mudah dibaca, tidak bermakna negatif, dan tidak multitafsir"; Art. 5(1)
"menggunakan huruf latin sesuai dengan kaidah bahasa Indonesia"; Art. 5(3) a name may not be abbreviated unless the
meaning is unchanged, and may not use "angka dan tanda baca"; Art. 8 "Pencatatan Nama pada Dokumen Kependudukan yang telah
dilaksanakan sebelumnya, dinyatakan tetap berlaku." The Dirjen Dukcapil was reported (secondary only) to call the
two-word rule an "imbauan" (appeal).

- **Parts:** one `FullName`, as Dukcapil records one "Nama Lengkap". No family/given split exists in the records.
- **Letters:** `\p{sc=Latin}` (Art. 5(1)), words joined by `-`, `'` or a space as the package's Latin default. A full
  stop after or between letters of a word is also allowed (`Moh. Hatta`, `M.A. Rahman`, as `Name\MX`): Art. 5(3) bans
  punctuation in a new record, but abbreviated forms and initials are in recorded names, which Art. 8 keeps valid.
- **Not enforced:** the two-word minimum (the brief's test: a one-word name such as `Suharto` is accepted), the
  60-character maximum (the package cap of 100 characters applies), the digit/punctuation ban beyond the character set
  above, and "easy to read / no negative meaning". All bind new records only; a recorded name stays valid.
- **Latin form (decision):** the Imigrasi rule for splitting a name into the primary and secondary identifier was not
  found. The only Imigrasi text read is a regional office's page, without legal basis: "nama yang tercantum pada paspor
  harus terdiri dari dua suku kata" and a one-word birth-certificate name is completed with the father's name (for an
  Umrah visa); it says nothing on the split, and a rule for new applications could not refuse a valid name in any case.
  So `Name\ID\PersonName` requires `Icao\PersonName $latin` and never derives it, like the mandatory-Latin group.
  Shape `{name, latin}` with its own codec.

## NIK (`Document\ID\NationalIdentityNumber`)

Ditjen Dukcapil blog, on PP 40/2019 Art. 1(7): NIK is a 16-digit number "yang bersifat unik atau khas, tunggal, dan melekat
pada seseorang". "6 digit pertama merupakan kode wilayah, yang terdiri atas 2 digit angka pertama sebagai kode provinsi,
2 digit angka selanjutnya kode kabupaten/kota, 2 digit selanjutnya sebagai kode kecamatan." "Angka 6 digit kedua
merupakan tanggal, bulan, dan tahun kelahiran" and "khusus untuk perempuan tanggal lahirnya ditambah angka 40." "Angka 4
digit terakhir merupakan nomor urut penerbitan NIK yang diproses secara otomatis dengan Sistem Informasi Administrasi
Kependudukan yang biasa disebut SIAK." The same text is Perda Wonosobo 6/2009 Art. 22(1), restating PP 37/2007 Art. 37.

- **Enforced:** 16 digits; digits 7-12 are `DDMMYY`, a woman's `DD` being day + 40.
- **Sex:** day 41-71 is a woman (`Sex::Female`), 1-31 a man. A day of 32-40 or above 71 is no day in either coding.
- **Date validity (decision):** the text says the six digits are the *tanggal, bulan, dan tahun kelahiran*; a day and
  month that are no calendar date (30 February, 31 April, month 13, day 70 for a woman) are not a birth date, so they are
  refused, as the brief asks. Real NIKs with an impossible date are not documented either way; flagged to the owner.
- **Century:** none quoted, the year is two digits: no `birthDate()` and no `HasBirthDate`; `birthDay()`, `birthMonth()`,
  `birthYearDigits()` (a string, `00` kept). 29 February is accepted when the two digits are divisible by four (`00` as
  2000), the package's `TwoDigitYearDate` rule.
- **Region:** `regionCode()` gives the six digits as a string. They are the Kemendagri region code of the place of
  residence at registration, not an ISO 3166-2 code, and are not checked against a list.
- **Serial:** the last four digits are not read (no `serial` accessor).
- `masked()` keeps four digits, no official rule; no display form is quoted, so there is no `formatted()`.
- **Also:** Korlantas has issued the SIM number as the NIK since July 2024 (below); DJP uses the NIK as the NPWP of an
  individual resident (below).

## NPWP (`Document\ID\TaxpayerNumber`)

DJP SP-43/2022: PMK 112/PMK.03/2022 has three forms: resident individuals use the NIK; "wajib pajak orang pribadi bukan
penduduk, wajib pajak badan, dan wajib pajak instansi pemerintah menggunakan NPWP format 16 (enam belas) digit"; branches
use "Nomor Identitas Tempat Kegiatan Usaha". "tinggal menambahkan angka 0 di depan NPWP lama atau format 15 digit". DJP
PENG-6/PJ.09/2024: the 15-digit NPWP or the NIK is used in the administration until the transition ends (PMK 136/2023);
the 15-digit form is written `00.000.000.0-000.000` in the forms it prescribes (items 5a, 6a1).

- **Decision:** one class for both, named `TaxpayerNumber` (the NPWP), accepting 15 digits or 16 digits. A 16-digit
  number is a NIK, a "0" + 15 digits, or a branch number, and cannot be told apart without rules DJP does not publish, so
  it is not read; the class does not claim to be only the 15-digit or only the NIK form. `isFifteenDigits()` tells them.
- **Placeholders refused:** PENG-6/PJ.09/2024 item 5a: "kolom NPWP pembeli BKP/penerima JKP diisi dengan
  00.000.000.0-000.000" when the buyer is identified by NIK; item 6b: "Elemen data NIKPemegangRek atau NIK_CP diisi dengan
  0000000000000000" for a non-resident individual (items 6a1 and 6a2 repeat the 15-digit form for a NIK holder). They mean
  "no number", so `000000000000000` and `0000000000000000` (with any separators) are refused as a placeholder.
- **Format only otherwise:** no check digit and no meaning of the 15 digits is published (secondary sites list a KPP code
  and a branch code, never quoted from DJP).
- **Display form:** `99.999.999.9-999.999` (2.3.3.1-3.3) from the DJP form quoted above, for 15 digits; the dots are
  accepted as separators besides the package's. PENG-6 item 6a1 prints the mask over two lines (`00.000.000.0-` then
  `000.000`); the text read has no mask without the hyphen, but the hyphen is only a separator, so `01.234.567.8901.000`
  (and the digits alone) parse to the same value. No display form of 16 digits is quoted: `formatted()` gives the digits.
- `masked()` keeps four digits, no official rule.

## Family card number (`Document\ID\FamilyCardNumber`)

Ditjen Dukcapil: "nomor KK terdiri dari 16 digit angka"; it stays while the head of family does not change and is not
inherited. Format only: a district Dukcapil page (Klaten) reads the digits as a region code, the issue date `DDMMYY` and a
serial number, with no legal basis; the national text read quotes no layout, so no date is checked.

## Not covered

- **SIM (driving licence) number:** Perpol 5/2021 (read in full) lists a "nomor registrasi penerbitan" among the
  recorded data and no layout; Perkap 9/2012 Art. 41(2) (quoted from pasal.id) says only that the number follows "nomor
  urut penerbitan SIM pada tahun berjalan". The old 12-digit layout appears only on aggregators. Korlantas states that the
  SIM number is the NIK since July 2024 and that old SIMs keep their numbers until renewal ("penggunaan NIK sebagai nomor
  SIM akan otomatis ketika melakukan perpanjangan"). A class of the NIK form would refuse the old numbers, and a class
  of the old form has no quotable rule: use `NationalIdentityNumber` for a new SIM.
- **Passport number:** no Ditjen Imigrasi text with the layout was found (the passport instruction pages, the FAQ and
  Permenkumham 20/2022 on stamps hold none; PERDIM 11/2020 and Permenkumham 8/2014 were unreachable). Secondary
  sources (Microsoft Purview) say 1-2 letters and 6-7 digits; not quoted from an official body.
- **BPJS Kesehatan number:** the 13 digits appear only in regional government articles and secondary sites; the
  BPJS Kesehatan manual says "No. JKN" without a length, and reports say the NIK is the participant number since 2022-2025.
