# South Korea — sources

All dates UTC. Sources read 2026-11-17.

| Source | Publisher | Where read |
|---|---|---|
| 주민등록법 시행규칙 Art. 2 (주민등록번호의 부여), as amended 2017-05-29 and 2020-10-05 | 행정안전부 | text on <https://www.nepla.ai/law/%EC%A3%BC%EB%AF%BC%EB%93%B1%EB%A1%9D%EB%B2%95-%EC%8B%9C%ED%96%89%EA%B7%9C%EC%B9%99> (an aggregator: "quoted from"); `www.law.go.kr` serves the page without the article text to WebFetch and `curl` |
| 주민등록증 (한국기록 · 국가기록원 theme page) | 국가기록원 | <https://theme.archives.go.kr/next/koreaOfRecord/identityCard.do> |
| 주민번호 뒷자리 지역표시번호 10월부터 폐지 (2020-05-25) | 행정안전부, 정책브리핑 | <https://www.korea.kr/news/policyNewsView.do?newsId=148872723>; announcement of 2019-12-17 <https://www.mois.go.kr/frt/bbs/type010/commonSelectBoardArticle.do?bbsId=BBSMSTR_000000000008&nttId=74731> |
| 출입국관리법 시행규칙 [별표 2] 외국인등록번호의 구성체계(제46조제3항 관련) | 법무부 | <https://law.go.kr/LSW/flDownload.do?flSeq=89453591&bylClsCd=110201> (PDF, one figure, read as an image with `pdftoppm`) |
| 2023. 4. 1.부터 외국인등록증이 새롭게 바뀝니다 (보도자료, 2023-03-28; posted by 이민정보과, PDF attachment) | 법무부 | <https://www.moj.go.kr/bbs/moj/182/568998/artclView.do>, the PDF <https://www.moj.go.kr/bbs/moj/182/451877/download.do> (read with `curl` and `pdftotext` 2026-11-17 07:30 UTC); a copy is hosted at music.snu.ac.kr ("quoted from" only, not used) |
| 차세대 전자여권 (passport design page) | 외교부 여권안내 | <https://www.passport.go.kr/home/kor/onlineExperience/index.do?menuPos=58>, read through the Internet Archive copy of 2023 <https://web.archive.org/web/2023/https://www.passport.go.kr/home/kor/onlineExperience/index.do?menuPos=58> |
| 차세대 전자여권 올해 12.21.(화)부터 전면 발급 개시 (2021-11-11) | 외교부 | <https://www.mofa.go.kr/www/brd/m_4080/view.do?seq=371764> |
| 운전면허번호 표기방식 변경 안내 ('14. 07.02), registered 2014-07-14 | 도로교통공단 (KoROAD) | Internet Archive copy of <https://www.koroad.or.kr/kp_web/licNewsView.do?board_code=PRBBS_080&board_num=101697> (`https://web.archive.org/web/2020/…`) |
| 운전면허번호 지역표기 변경 계획 (2014-10-16), 변경 안내 (2014-04-15) | 외교부 embassy sites (reprints of KoROAD) | <https://overseas.mofa.go.kr/us-ko/brd/m_4475/view.do?seq=1102877>, <https://overseas.mofa.go.kr/fi-ko/brd/m_9610/view.do?seq=1064435> |
| 자녀의 이름 (찾기쉬운 생활법령정보) | 법제처 | <https://www.easylaw.go.kr/CSP/CnpClsMain.laf?csmSeq=1830&ccfNo=2&cciNo=1&cnpClsNo=2> |
| 가족관계의 등록 등에 관한 법률 Art. 44(3), 가족관계의 등록 등에 관한 규칙 Art. 37 | 법원행정처 | quoted in the 생활법령 page above; `www.law.go.kr` article text not served |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6 | ICAO | see `ICAO.md` |

Reachability: `www.law.go.kr` pages render only a shell (no article text) for WebFetch and `curl`, and the DRF API
needs a registered key; `lbox.kr`, `bigcase.ai`, `casenote.kr` answer 403 or close the socket. `www.passport.go.kr`
answers 500 (live) and times out for `curl`; `www.koroad.or.kr` and `www.ddm.go.kr` time out for `curl` (the Internet
Archive copies read). Secondary only (never a rule, never cited in code): Wikipedia "Resident registration number" and
"South Korean identity card", 한국일보 and 거제타임즈 articles, namu.wiki, blogs.

## Names

法 Art. 44(3), as quoted by 생활법령: "자녀의 이름에는 한글 또는 통상 사용되는 한자를 사용해야 합니다". The range of Hanja is Art. 37(1) of the
rules (인명용 한자); a Hanja outside it is recorded in Hangul. 생활법령, 예규 "이름의 기재문자와 관련된 가족관계등록사무" Art. 4(a):
"이름자가 5자(성은 제외)를 초과하면 출생신고가 수리되지 않습니다", with exceptions for a child of a foreign father or mother and for "a name a
foreigner used abroad before naturalization, nationality acquisition, or restoration".

- **Letters:** `\p{sc=Hangul}` letters (`\p{Lo}`), so the tone marks U+302E/U+302F (Script=Hangul, Mc) are refused
  (code review CR-005). Between words of a foreign name the katakana middle dot U+30FB and the bullet U+2022 are stored
  as `·` U+00B7, as in CN (CR-021). Hanja names are not covered (the 인명용 한자 list is a table of the rules, and a name is
  recorded in Hangul when a character is outside it). No rule found on 한글 자모 standing alone or on spelling.
- **Standalone jamo (decision, not quoted):** a compatibility jamo (`ㄱ` U+3131, `ㅏ` U+314F, halfwidth U+FFA1) has NFKC ≠
  NFC and is refused as a compatibility character like fullwidth Latin; a conjoining jamo that NFC leaves uncomposed
  (`ᄀ` alone, a second final consonant) is refused because it completes no syllable. Decomposed jamo that do compose
  (`ᄀ`+`ᅡ`+`ᆨ` → `각`) are accepted. No official text decides either; both refusals follow from "a name is written in
  syllables" and the package's compatibility rule.
- **5-syllable limit:** not enforced: it binds a *new* registration and has exceptions (a still-valid name is never
  refused).
- **Family / given split:** no rule found that makes the split absent or present for every name; a naturalised person
  may keep the name used abroad (the exception quoted above), written in Hangul as one name with spaces. So the given
  name is nullable as in CN: a name with no split is stored whole in the family part. One space, `·` U+00B7 or hyphen
  between words is allowed (a decision of the owner; no official text read settles the separators of such a name).
  `nationalSeparator()` is `''`: family and given are written together (`김민준`). `full()` joins the two with no
  space also for a naturalised multi-word name split into family and given (존슨 + 마이클 gives 존슨마이클); such a
  name can be stored whole in the family part instead (`존슨 마이클`, given null), which keeps its space.
- **Passport spelling:** the Latin form is mandatory, never derived, never compared with the Hangul (Kim, Gim, Yi, Lee).

## Resident registration number (`Document\KR\ResidentRegistrationNumber`)

- **시행규칙 Art. 2** (as quoted by the aggregator, "quoted from"): "「주민등록법」(이하 “법”이라 한다) 제7조의2제1항에 따른 주민등록번호는 생년월일ㆍ성별 등을표시할
  수 있는 13자리의 숫자로 부여한다. <개정 2017. 5. 29., 2020. 10. 5.>". The article has no list of the seventh digit's values (the
  premise that the 시행규칙 decodes the digits is wrong): the layout is 13 digits.
- **국가기록원:** "13자리 주민등록번호 앞의 6자리는 생년월일, 뒷부분 7자리는 성별과 지역코드, 검증번호로 이루어진다"; "1은 남자, 2는 여자이다"; "2000년 출생자부터는남자는 3, 여자는 4를
  부여받았다". So 1, 2 (1900s) and 3, 4 (2000s) are decoded from an official page.
- **9 and 0 (1800s):** quoted from secondary sources only (Wikipedia "Resident registration number": 9 and 0 for
  1800-1899; 한국일보 2025-04-16 and 거제타임즈 repeat it), found in no government text. They are decoded the same way (9 man, 0
  woman, in the 1800s) and nothing else rests on them. 5-8 are accepted only by `ForeignerRegistrationNumber` (below).
- **After 2020-10:** 행정안전부, 2020-05-25: "오는 10월부터 주민등록번호를 신규로 부여받거나 변경하는 경우 뒷자리는 성별 표시 첫 자리를 제외하고 6자리의 임의번호를 부여받게 된다"
  ("지역번호를 폐지하고 뒷자리 성별 뒤 6자리에 임의번호를 부여한다"). The old check digit is therefore **not enforced**: also its formula is not
  quoted by any government text read ("검증번호 … 계산"), so a number with a wrong last digit is accepted.
- **Birth date:** must exist in the century of the seventh digit (`checkdate`): 29 February 1900 refused, 2000 accepted.
- **Mask:** no 개인정보보호위원회 / 행정안전부 masking text found (a search of go.kr sites returned none); last 4: `*********4567`.
- No display form is quoted: no `formatted()`.

## Foreigner registration number (`Document\KR\ForeignerRegistrationNumber`)

**출입국관리법 시행규칙 별표 2 (read as an image):** the figure shows `?????? - ?  ??  ??  ??  (13자리)` with the groups labelled
생년월일, 성별구분번호, 등록연도번호, 등록기관번호, 일련번호. 13 digits; the values of 성별구분번호 are not listed there.

**법무부 보도자료 of 2023-03-28 (new 외국인등록증 from 2023-04-01), official:** the footnote reads "* 외국인등록번호 13자리 중 7번째 숫자가 5․7은 남성,
6․8은 여성" (the card no longer prints the sex, "외국인등록번호로 구분이 가능한 성별*을 생략하여 주민등록증과의 통일성을 확보"). So the sex of 5/7 and 6/8 is
official. The release says nothing of the century: 5 and 6 for 1900-1999 and 7 and 8 for 2000-2099 are quoted from
secondary sources only (Wikipedia "Resident registration number"), so only that split is secondary. Coverage: the
release names "외국인등록증(영주증, 외국국적동포 국내거소신고증 포함)" as cards with the new design and gives no other number layout, so it adds
no document; the 영주증 and 국내거소신고증 numbers are not described anywhere read and are not covered (a card of either that
shows an 외국인등록번호 is the same number).

Two classes: the resident class refuses 5-8 and this one refuses 1-4, 9 and 0 (the name must not claim more than it
covers); the owner confirmed the split.

**1.0 (ruling B4):** the century is not official, so the class no longer implements `HasBirthDate`. It gives
`birthYearDigits()`, `birthMonth()` and `birthDay()`, and a birth date is refused only when it exists in no century
(`Internal\TwoDigitYearDate`: 29 February `00` is accepted with any of 5-8; it was refused with 5 or 6 in 0.x). The
resident class keeps `HasBirthDate`: its digits 1-4 are decoded from an official page; 9 and 0 stay secondary (above). Open question for the owner (no official text read): a foreigner number whose
birth date is not a calendar date (for instance a registered unknown month or day) is refused here; kept as decided, to
revisit if an official practice for unknown dates is found. Mask: last 4.

## Multiple-entry passport number (`Document\KR\MultipleEntryPassportNumber`)

MOFA 여권안내, 차세대 전자여권 (Internet Archive copy 2023): "여권번호 체계를 변경(M12345678 → M123A4567)하고, 주민등록번호를 삭제하며, 월(月) 표기방법을 변경(영문
→ 한글/영문) 했습니다." The 2021-11-11 release: "외교부는 올해 12월 21일부터 일반 국민을 대상으로 … 차세대 전자여권 발급을 개시합니다." So `M` + 8 digits, and `M`
+ 3 digits + a letter + 4 digits. The guide shows an `M` number of the multiple-entry (복수) passport; the class is named
for what the guide shows and covers that series only. The letter's set and a check character are not published: any
letter. Mask: last 4. Not covered (prefix unpublished): the single-use and emergency (단수, 긴급) passports, official,
diplomatic and other series.

## Driving licence number (`Document\KR\DriverLicenceNumber`)

KoROAD, 2014-07-14: "2014년 7월 2일부터 새로 발급되는 운전면허증에는 면허번호의 지역명이 숫자로 표기됩니다. 11 : 서울 12 : 부산 13 : 경기 14 : 강원 15 : 충북 16 : 충남
17 : 전북 18 : 전남 19 : 경북 20 : 경남 21 : 제주 22 : 대구 23 : 인천 24 : 광주 25 : 대전 26 : 울산". The embassy reprints (2014-04-15 and
2014-10-16) give the example `서울 95-255933-61 → 11-95-255933-61` (so 12 digits, the display form with hyphens is quoted:
`formatted()`), and note "광주의 경우 운전면허시험장이 없는 관계로 전남(18)에 해당" (the 2014-10-16 table omits 24). The region number is a
string (`regionCode()`), not ISO 3166-2, and is not checked against the list (the lists differ and later codes exist).
The meaning of the 2nd group, the 6-digit group and the last two digits is not quoted: no accessor, no rule, no check
digit.

**Earlier form (before 2014-07-02):** the licence shows the region's name, `서울 95-255933-61` (the left side of the
quoted example). It stays valid, so it is a second shape, stored as KoROAD prints it (`서울 95-255933-61`, region name, a
space, two digits, hyphen, six digits, hyphen, two digits; other separators and fullwidth digits are normalised, Hangul
is NFC-composed). The 16 names are those of KoROAD's table above and the table maps each to its number for
`regionCode()`: 서울 11, 부산 12, 경기 13, 강원 14, 충북 15, 충남 16, 전북 17, 전남 18, 경북 19, 경남 20, 제주 21, 대구 22, 인천 23, 광주 24, 대전 25,
울산 26. Another name is refused. 광주 follows KoROAD's own table (24); the embassy reprint's note that 광주 "에 해당 전남(18)" (no
test centre) is not applied. `formatted()` of the earlier form is the stored form: the example prints it that way. A
number of the earlier form and its 12-digit successor are two values (the region name and the number of the same office
are not equated: the other digits are the same but the package does not assume the licence was reissued). Mask: last 4
digits of the 12-digit form, for both forms (`********3361`; code review CR-022), so a masked value tells neither the
region nor the form.

## Not covered

Hanja names; passports of other series, including the single-use and emergency (단수, 긴급) ones; the 영주증 and 국내거소신고증
numbers as such; 법인등록번호, 사업자등록번호 (not candidates).
