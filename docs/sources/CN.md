# China — sources

All dates UTC. Sources read 2026-11-17.

| Source | Publisher | Where read |
|---|---|---|
| GB 11643-1999 《公民身份号码》 (approved 1999-01-19, in force 1999-07-01), §5.1-5.2, Annex A and B | 国家质量技术监督局 (Standardization Administration of China) | <http://zjw.beijing.gov.cn/Portals/0/zhuanti/fazhan/%E8%BA%AB%E4%BB%BD%E8%AF%81GB11643-1999.pdf> (Beijing Municipal Commission of Housing and Urban-Rural Development; a scanned PDF, read as page images) |
| 《港澳台居民居住证申领发放办法》 (国办发〔2018〕81号, in force 2018-09-01), Art. 3 | 国务院办公厅 | <https://www.gov.cn/zhengce/content/2018-08/19/content_5314865.htm> |
| 附件 2 "新版外国人永久居留身份证适配性改造要点" (the new card in use from 2023-12-01), §二, table 1 | 国家移民管理局 (NIA) | <https://www.nia.gov.cn/20231013/2.pdf> (text PDF, read with `pdftotext`) |
| 《出入境证件简明手册》 | NIA | <https://www.nia.gov.cn/n794014/n1050181/n1050479/c1197066/content.html> |
| 《公安机关电子普通护照签发量即将破亿 新号段证件预计于4月启用》 (2017-04-07) | 公安部 website, republished by 国家互联网信息办公室 | <https://www.cac.gov.cn/2017-04/07/c_1120766993.htm> |
| 《电子往来港澳通行证号码编制规则调整》 (2025-10-15, "来源：国家移民管理局") | NIA, republished by 福建省公安厅 | <https://gat.fujian.gov.cn/ztzl/cryjfwwj/flfg_28281/201812/t20181205_4693182.htm> |
| "台胞证与回乡证的编码规则", NIA citizen exit-entry department's reply of 2021-09-23 | NIA | <https://www.nia.gov.cn/Enquiry/publish/showQuestion.jsp?MZ=5tIXYbRpaLKgbVSmyF9JoA%3D%3D> |
| 《居民身份证法》 (2003, amended by the decision of the NPC Standing Committee of 2011-10-29, 主席令第五十一号), Art. 23 para. 2 | 全国人民代表大会常务委员会; text read on government reprints | <https://www.longling.gov.cn/info/11834/2365289.htm> (Longling County government) and <http://sft.jiangsu.gov.cn/art/2011/11/24/art_71924_8102502.html> (Jiangsu Department of Justice), both read 2026-11-17 |
| ICAO Doc 9303 Part 3, 8th ed. (2021), §4.6 | ICAO | see `ICAO.md` |

Reachability: `zjw.beijing.gov.cn`, `www.gov.cn`, `www.nia.gov.cn` and `www.cac.gov.cn` answer WebFetch (the NIA and GB
PDFs are saved locally and read with `pdftotext` and as images; `curl` from the sandbox is refused by `www.nia.gov.cn`
and `www.npc.gov.cn`: 403). `www.npc.gov.cn` fails WebFetch with an SSL handshake error and its Internet Archive copy is
refused by the fetch tool ("unable to fetch from web.archive.org"); `flk.npc.gov.cn` gives no link to the law
(2026-11-17); `www.shaanxi.gov.cn` and `ga.sz.gov.cn` closed the socket. Art. 23 was therefore read on the two
government reprints listed above, not on npc.gov.cn. `www.mfa.gov.cn` (page of 公安部's 2012 announcement) returns "page
deleted"; `www.gov.cn/jrzg/2012-05/31/content_2149327.htm` returns 404; `www.hmo.gov.cn` has no such notice. Secondary
only (never a rule, never cited in code): Wikipedia, ifeng.com and China Daily articles on the name dot, ID generator
and validator sites.

## Citizen identity number (`Document\CN\CitizenIdentityNumber`)

**GB 11643-1999, §5.1** (verbatim): "公民身份号码是特征组合码，由十七位数字本体码和一位数字校验码组成。排列顺序从左至右依次为：六位数字
地址码，八位数字出生日期码，三位数字顺序码和一位数字校验码。"

- **5.1.1 地址码:** "表示编码对象常住户口所在县（市、旗、区）的行政区划代码，按GB/T 2260的规定执行。" The GB/T 2260 list is not part of this text: the address
  code is read, not checked (`administrativeDivisionCode()`).
- **5.1.2 出生日期码:** "表示编码对象出生的年、月、日，按GB/T 7408的规定执行。年、月、日代码之间不用分隔符。例：某人出生日期为1966年10月26日，其出生日期码为19661026。" Four-digit
  year, so the date is a calendar date (`checkdate`): 1900-02-29 is refused, 2000-02-29 accepted. Calendar check under
  the 1.0 rule (README "Birth dates"): the national standard binds the issuer (国办发〔2018〕81号 Art. 3: "由公安机关按照公民身份号码
  国家标准编制") and refers the date to GB/T 7408. Annex A2 is of a man
  born 1880-01-01 (`440524188001010014`): no lower bound on the year.
- **5.1.3 顺序码:** "…顺序码的奇数分配给男性，偶数分配给女性。" `sex()`: the last digit of the three, odd = `Sex::Male`.
- **5.1.4 校验码:** "校验码采用ISO 7064:1983，MOD 11-2校验码系统。" 5.1.4.1: Σ(aᵢ×Wᵢ) ≡ 1 (mod 11), i from the right, "Wᵢ＝2^(i-1) (mod
  11)", table 1 weights for i = 18…1: 7 9 10 5 8 4 2 1 6 3 7 9 10 5 8 4 2 1. 5.1.4.2:
  "aᵢ…取值范围是0≤a₁≤10；当a₁值等于10时，用罗马数字符X表示", table 2 (Σ mod 11 → check): 0→1, 1→0, 2→X, 3→9, 4→8, 5→7, 6→6, 7→5, 8→4, 9→3,
  10→2. This is what `Checksum::iso7064Mod11Dash2` computes (its test has `11010519491231002` → `X`).
- **5.2:** "公民身份号码的各特征码依次连接，不留空格" — no display form, so no `formatted()`.
- **Annex A examples:** A1 `11010519491231002X` (a woman, Beijing Chaoyang, 1949-12-31); A2 `440524188001010014` (a man,
  Guangdong, 1880-01-01). Annex B1 computes the check of `11010519491231002`.
- **Lower-case `x`:** the standard writes `X`; a lower-case `x` is the same character typed without Shift, so it is
  stored as `X` (the package upper-cases every number). Fullwidth digits and `Ｘ`/`ｘ` fold through `Internal\Digits`.
- **15-digit number:** the 1989 standard's number of the first-generation card is not part of GB 11643-1999. 《居民身份证法》
  Art. 23 para. 2 (amended 2011-10-29), verbatim on both reprints:
  "依照《中华人民共和国居民身份证条例》领取的居民身份证，自2013年1月1日起停止使用。依照本法在2012年1月1日以前领取的居民身份证，在其有效期内，继续有效。" (the Jiangsu copy writes the dates
  in fullwidth digits) So it is refused by length (the message carries the character count, not the input); the refusal
  rests on the 18-character layout of GB 11643-1999 §5.1 either way.
- **HK/MO/TW resident permit (国办发〔2018〕81号, Art. 3):**
  "公民身份号码由公安机关按照公民身份号码国家标准编制。香港居民公民身份号码地址码使用810000，澳门居民公民身份号码地址码使用820000，台湾居民公民身份号码地址码使用830000。" The permit's number is
  a GB 11643 number, so `CitizenIdentityNumber` takes it and `administrativeDivisionCode()` returns
  `810000`/`820000`/`830000`. The rule "an address code starting 81, 82 or 83 must be exactly 810000, 820000, 830000" is
  not stated in either text and is not enforced.
- **Mask:** no official rule; last four characters (`**************002X`). The last four include position 17, the
  last digit of the sequence code, so a masked number still tells the sex by its parity (code review CR-023: the
  package's default mask rule is kept, as no binding CN masking rule is quoted). **Not adopted (docs only, read
  2026-11-24 UTC):** JR/T 0171-2020 "个人金融信息保护技术规范" (中国人民银行, in force 2020-02-13), 附录A (资料性附录)
  信息屏蔽, verbatim: "信息隐藏规则（缺省）：显示前 1/3 和后 1/3（向下取整），其他用*号代替，这样保留了部分信息，并且保证了信息的长度不变性，
  对信息持有者更易辨别，如手机、身份证号码等。" Table A.1, row "1）身份证号码、军官证号码、护照号码": "使用缺省信息隐藏规则，如隐藏出生日期，身份证号码屏蔽后 6
  位". The annex is informative (资料性), a financial-industry standard, read from a bank's copy
  (<https://aibank.com/upload/attachs/2022/12/5-个人金融信息保护技术规范.pdf>, not a PBOC host), and inconsistent with
  itself (the default shows 6 + 6 of 18 characters; the table example hides the birth date, positions 7-14, or "the
  last 6"). Its default shows **more** characters than the current mask, and `masked()` may hide more in a minor,
  never less. GB/T 35273-2020 § 7.2 states only the principle ("个人信息控制者宜对需展示的个人信息采取去标识化处理等措施").

## Foreigner's permanent residence card (`ForeignerPermanentResidenceNumber`, `LegacyForeignerPermanentResidenceNumber`)

**NIA annex 2, §二 item 2** (verbatim): "新版永居证参照居民身份证“公民身份号码”国家标准，在证件号码构成与表现形式方面作了调整，由15位（3位拉丁
字母+12位数字）调整为18位（17位数字+1位校验位），原号码构成中，代表国家和地区的3位拉丁字母代码转为数字代码。" Item 3: "新版永居证号码首位设置为
“9”，作为外国人的身份标识码…新版永居证号码最后一位采用MOD11-2算法设置为校验码，与公民身份号码校验方式相同。" The new card is "2023年12月1日正式
启用签发"; earlier cards ("外国人永久居留证和2017版外国人永久居留身份证") stay valid until expiry.

**Table 1, new form** (example): "1985年1月1日出生的一沙特阿拉伯男性公民，第一位在江苏省申领新版永居证，其证件号码为：932682198501010017" = 身份标识码
`9`, 受理地代码 `32` (Jiangsu), 国家和地区代码 `682` (Saudi Arabia), 出生日期码 `19850101`, 顺序码 `001`, 校验码 `7`. Enforced: 18
characters, the leading `9`, a calendar birth date at positions 7-14, the MOD 11-2 check character (it gives `7` for
the example). Not enforced: the two code lists (not named; `682` equals the ISO 3166-1 numeric code of Saudi Arabia but
the NIA does not say so), so `applicationPlaceCode()` and `nationalityCode()` are strings. The sequence code has no
sex meaning in the text, so there is no `HasSex`.

**Table 1, earlier form** (example): "1990年12月31日出生的一哈萨克斯坦男性公民，第一位在北京市申领2017版永居证，其证件号码为：KAZ110090123105" = 国家和地区代码
`KAZ`, 地址码 `1100`, 出生日期 `901231` (two-digit year), 顺序码 `0`, 校验码 `5`. Format only: 3 letters and 12 digits. No
check algorithm is published, and the two-digit year has no century, so the date is not read.

Mask: no official rule; last four characters.

## Ordinary passport number (`OrdinaryPassportNumber`)

NIA 《出入境证件简明手册》 (verbatim, via WebFetch): "2017年4月以前签发的电子普通护照号码由“E”+8位阿拉伯数字组成。"; "第一位仍是前缀字母“E”，第二位
改为顺序使用英文字母（“I”、“O”除外），第三位起7位阿拉伯数字，护照号码总位数仍为9位。"; "电子普通护照启用前签发的旧版普通护照，号码为“G”+8位阿拉伯数字。"
公安部's notice of 2017-04-07 (cac.gov.cn): "证件号码中前缀字母E保持不变，第二位改为顺序使用英文字母（I、O除外），第三位起仍然为阿拉伯数字，
总位数仍为9位。新号段电子普通护照的防伪特征没有变化，起始号码为第EA0000001号。" Accepted: `E` + 8 digits; `E` + a letter other than `I`,
`O` + 7 digits; `G` + 8 digits. No check character is published. The class is named for the ordinary
(普通) passport only; the other kinds are listed under "Not covered".
Mask: no official rule; last four characters.

## Exit-entry permits (`ExitEntryPermitToHongKongMacauNumber`, `ExitEntryPermitToTaiwanNumber`)

- **往来港澳通行证 (the mainland resident's "港澳通行证"):** NIA, 2025-10-15:
  "2014版往来港澳通行证（电子往来港澳通行证）于2014年5月上线启用，证件号码由前缀字母C和8位阿拉伯数字组成…现有号段资源使用完毕后，电子往来港澳通行证号码编制规则将进行适当调整，其中前缀字母C保持不变，
  第二位改为顺序使用英文字母（I、O除外），第三位起仍然为阿拉伯数字，总位数仍为9位。新号段证件起始号码为第CA0000001号。"
  The 简明手册 says the same for permits before 2018-12-03. Accepted: `C` + 8 digits and `C` + a letter other than `I`, `O`
  + 7 digits (the new range has not started; accepting it now avoids refusing a valid number when it does).
- **往来台湾通行证 (the "台湾通行证"):** 简明手册: "它的通行证号码编码规则是由前缀字母“L”+8位阿拉伯数字组成。"; the older permit: "…由前缀字母“T”+8位阿拉伯数字组成。" Accepted:
  `L` or `T` + 8 digits.
- No check character is published for either. Mask: no official rule; last four characters.

## Not covered

| Candidate | Reason |
|---|---|
| 港澳居民来往内地通行证 (回乡证, `H`/`M` + 8 digits; non-Chinese holders `HA`/`MA`, 9 characters) and 台湾居民来往大陆通行证 (台胞证, 8 digits) | Formats are quoted (NIA reply of 2021-09-23, 简明手册), but they are not in the plan's candidate list; add them in a later iteration if wanted. The `HA`/`MA` shape beyond "9 characters" is not published. |
| First-generation 15-digit citizen number | No longer valid since 2013-01-01 (Art. 23 above); refused by length. |
| Official (公务), diplomatic (外交) and public-affairs (因公) passports, Hong Kong and Macao SAR passports, and the series of passports other than the ordinary `E`/`G` | No format published in the sources read; a number of these kinds would be refused by `OrdinaryPassportNumber`, hence its name. |
| Check digit of the 15-character permanent residence number | Not published. |
| Driving licence | The licence number is the holder's citizen identity number (plan decision): use `CitizenIdentityNumber`. |
| Hukou, social credit code, military ID | Outside the plan. |
| Uyghur, Tibetan and Mongolian script names | Plan decision: not covered. |

## Names (`Name\CN\*`)

- **Parts:** `FamilyName` and `GivenName`, letters `\p{sc=Han}` that are `\p{Lo}` (plan decision, tightened by code
  review CR-007). Script=Han also holds non-letters, refused as such: CJK radicals supplement `⻄` U+2EC4 (So, looks like
  `西`), the Hangzhou numerals `〡`-`〩` U+3021-3029 (Nl), the iteration marks `々` U+3005 and `〻` U+303B (Lm). `〇` U+3007
  (Script=Han, Nl) is accepted explicitly: it occurs as a character in Chinese names (controller's ruling on the code
  review). NFC folds the CJK compatibility ideographs (`\u{F900}` → `豈`); a character whose NFKC form differs after NFC
  (fullwidth Latin, Kangxi radicals) is refused by `AbstractNamePart`. `full()` writes family and given together with no
  separator.
- **Minority-nationality names:** the middle dot `·` U+00B7 (`阿沛·阿旺晋美`) between Han words, never leading, trailing or
  doubled. The dot is not specified by any official text found: a Xinjiang government press conference of 2016-06
  (ifeng.com and China Daily reports, secondary) names the inconsistent encoding of the dot in the systems. The package
  follows the plan's decision: U+00B7 is stored, the katakana middle dot U+30FB and the bullet U+2022 fold to it before
  validation. Such a name has no family/given split in the ID card's single 姓名 field, so `PersonName` takes a nullable
  given name: the whole name (`阿沛·阿旺晋美`, a Tibetan name) is stored in `FamilyName` with `given` null, and `full()` is
  the family part alone. Payload `{family, given, latin}` with `given` null for it (the key stays required).
- **Latin form:** required, `Icao\PersonName`, never derived: a passport's pinyin is the holder's choice (Cantonese,
  Wade-Giles and Hokkien spellings are common). It is checked only for being a valid ICAO personal name (Doc 9303 Part 3
  §4.6). Payload `{family, given, latin}`.
