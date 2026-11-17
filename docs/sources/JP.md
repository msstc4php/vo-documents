# Japan — sources

All dates UTC. Sources read 2026-11-17.

## Names

| Source | Publisher | Where read |
|---|---|---|
| ヘボン式ローマ字綴方表, 【ヘボン式ローマ字表記へ変換する際の注意事項】, 【ヘボン式によらないローマ字氏名表記】, 【別名併記】 | 外務省 (MOFA) | <https://www.ezairyu.mofa.go.jp/passport/hebon.html> (read with `curl`; Internet Archive copy of 2025-01-09: <https://web.archive.org/web/20250109094317/https://www.ezairyu.mofa.go.jp/passport/hebon.html>) |
| 戸籍法 (昭和二十二年法律第二百二十四号), Art. 13, Art. 50 | 国会; text of e-Gov 法令検索 (デジタル庁) | <https://laws.e-gov.go.jp/law/322AC0000000224>, read through the e-Gov API <https://laws.e-gov.go.jp/api/1/lawdata/322AC0000000224> on 2026-11-17 (05:45 UTC; re-fetched 06:05 UTC to confirm the Art. 13 and Art. 50 quotes) |
| 戸籍法施行規則 (昭和二十二年司法省令第九十四号), Art. 30-2, Art. 60, 別表第二 (revision `322M40000010094_20260626_508M60000010043`, in force 2026-06-26) | 法務省; text of e-Gov 法令検索 | <https://laws.e-gov.go.jp/law/322M40000010094>, read through <https://laws.e-gov.go.jp/api/1/lawdata/322M40000010094> on 2026-11-17 (05:45 UTC); the two figures of 別表第二 read as images from <https://laws.e-gov.go.jp/api/2/attachment/322M40000010094_20260626_508M60000010043?src=./pict/2JH00000247973.jpg> (katakana) and `…?src=./pict/2JH00000247974.jpg` (記号) on 2026-11-17 |
| 子の名に使える漢字 | 法務省 民事局 | <https://www.moj.go.jp/MINJI/minji86.html> |

Reachability (2026-11-17): WebFetch of `www.ezairyu.mofa.go.jp` returns 403 and WebFetch refuses `web.archive.org`;
`curl` from the sandbox reads both (the live page and the archived copy give the same table). `www.moj.go.jp/content/001413974.pdf`
(a 振り仮名 leaflet named by a search engine) returns 404. Secondary only, never a rule: prefectural passport pages
(Yamanashi, Fukuoka, Kanagawa) that repeat the MOFA rules.

### 1. Characters of the national form

戸籍法 Art. 50 (verbatim): "子の名には、常用平易な文字を用いなければならない。 常用平易な文字の範囲は、法務省令でこれを定める。"
戸籍法施行規則 Art. 60 (verbatim): "戸籍法第五十条第二項の常用平易な文字は、次に掲げるものとする。 一 常用漢字表（平成二十二年内閣告示第二号）に掲げる漢字（括弧書きが添えられているものについては、括弧の外のものに限る。） 二 別表第三に掲げる漢字 三 片仮名又は平仮名（変体仮名を除く。）"
法務省, 子の名に使える漢字: "常用漢字表と人名用漢字表に掲げられた漢字は，いずれも子の名に使用することができます。"

These rules bind a *new* given name at birth registration; family names and names registered earlier use any kanji
of the 戸籍 (old forms, variant characters). A still-valid name is never refused, so the package does not enforce
the 常用漢字/人名用漢字 lists nor the exclusion of 変体仮名: the letters are `\p{sc=Han}`, `\p{sc=Hiragana}`,
`\p{sc=Katakana}`, the long-vowel mark `ー` U+30FC and `〆` U+3006 (both Script=Common, listed explicitly). The Art. 60
list binds only a new given name, and real surnames contain `〆` (`〆木`), so it is accepted in every name part (not in
a reading: 別表第二 has no `〆`, and it has no Hepburn spelling). `々` U+3005 has Script=Han and is reached through
`\p{sc=Han}` (`佐々木`). No separator: the 姓 and the 名 are separate fields, and a part has no
space, hyphen or dot. The katakana middle dot `・` U+30FB (Script=Common) is no letter of any of the texts and is
refused. Combining voicing marks U+3099/U+309A (Script=Inherited) are composed by NFC with the kana before them
(`か` + U+3099 → `が`); one left over after NFC (`ア` + U+3099 has no composed form) is refused. Halfwidth katakana
(`ｻｸﾗ`) and the spacing marks `゛` `゜` are compatibility characters and are refused like fullwidth Latin.

### 2. Readings (振り仮名)

戸籍法 Art. 13 (as amended; the 振り仮名 is recorded since 2025-05-26): "戸籍には、本籍のほか、戸籍内の各人について、次に掲げる事項を記載しなければならない。 一 氏名 二 氏名の振り仮名（氏に用いられる文字の読み方を示す文字（以下「氏の振り仮名」という。）及び名に用いられる文字の読み方を示す文字（以下「名の振り仮名」という。）をいう。以下同じ。）…" and "氏名の振り仮名に用いることができる仮名及び記号の範囲は、法務省令で定める。"
戸籍法施行規則 Art. 30-2: "戸籍法第十三条第三項の法務省令で定める氏名の振り仮名に用いることができる仮名及び記号の範囲は、別表第二に掲げるものとする。"
別表第二 (figures): "一 片仮名のうち次に掲げるもの" — ア to ン, ガ to ポ, ヴ, the small ァ ィ ゥ ェ ォ ャ ュ ョ ヮ ッ (no ヰ, ヱ); "二 記号" — "ー（長音記号）".

The 戸籍 writes the reading in katakana; passport applications and older records also give it in hiragana, so a
reading is hiragana or katakana (also mixed) plus `ー`, stored as given. The 別表第二 set is a rule for a new record and
is not enforced (a reading with `ヰ` stays valid). A reading is validated by character set only: one that starts with a
small kana, `ん` or `っ` is accepted, but has no Hepburn spelling (§3), so its name needs a Latin override.

### 3. Passport Latin form: MOFA Hepburn (`Name\JP\PassportHepburn`)

【別名併記】 (verbatim): "日本の旅券は、ICAO（国際民間航空機関）文書に準拠して作成され、旅券面の氏名は、戸籍に記載されている氏名を記載することとしています。"
The Latin form is derived from the readings, family `<<` given, katakana folded to hiragana for the derivation only.

**ヘボン式ローマ字綴方表** (every row; the package test has one data-provider case per row):

| | | | | |
|---|---|---|---|---|
| ア A | イ I | ウ U | エ E | オ O |
| カ KA | キ KI | ク KU | ケ KE | コ KO |
| サ SA | シ SHI | ス SU | セ SE | ソ SO |
| タ TA | チ CHI | ツ TSU | テ TE | ト TO |
| ナ NA | ニ NI | ヌ NU | ネ NE | ノ NO |
| ハ HA | ヒ HI | フ FU | ヘ HE | ホ HO |
| マ MA | ミ MI | ム MU | メ ME | モ MO |
| ヤ YA | | ユ YU | | ヨ YO |
| ラ RA | リ RI | ル RU | レ RE | ロ RO |
| ワ WA | ヰ I | | ヱ E | ヲ O |
| ン N(M) | | | | |
| ガ GA | ギ GI | グ GU | ゲ GE | ゴ GO |
| ザ ZA | ジ JI | ズ ZU | ゼ ZE | ゾ ZO |
| ダ DA | ヂ JI | ヅ ZU | デ DE | ド DO |
| バ BA | ビ BI | ブ BU | ベ BE | ボ BO |
| パ PA | ピ PI | プ PU | ペ PE | ポ PO |
| キャ KYA | | キュ KYU | | キョ KYO |
| シャ SHA | | シュ SHU | | ショ SHO |
| チャ CHA | | チュ CHU | | チョ CHO |
| ニャ NYA | | ニュ NYU | | ニョ NYO |
| ヒャ HYA | | ヒュ HYU | | ヒョ HYO |
| ミャ MYA | | ミュ MYU | | ミョ MYO |
| リャ RYA | | リュ RYU | | リョ RYO |
| ギャ GYA | | ギュ GYU | | ギョ GYO |
| ジャ JA | | ジュ JU | | ジョ JO |
| ビャ BYA | | ビュ BYU | | ビョ BYO |
| ピャ PYA | | ピュ PYU | | ピョ PYO |

"※参考": シェ SHIE, チェ CHIE, ティ TEI, ニィ NII, ニェ NIE, ファ FUA, フィ FUI, フェ FUE, フォ FUO, ジェ JIE, ディ DEI, デュ DEYU,
ウィ UI, ウェ UE, ウォ UO, ヴァ BA, ヴィ BI, ヴ BU, ヴェ BE, ヴォ BO, and again ヴァ BUA, ヴィ BUI, ヴェ BUE, ヴォ BUO; "※注意 「ヴァ:VA」「ヴィ:VI」「ヴ:VU」「ヴェ:VE」「ヴォ:VO」は使用不可".

The reference rows are used too (without them ファ would have no spelling). The ヴ rows have two spellings; the
derived form takes the kana-by-kana one, BUA/BUI/BUE/BUO, which MOFA's own example lists first ("ヴィヴィアン BUIBUIAN 又は
BIBIAN"); BA/BI/BE/BO are accepted as an override (§4). Designer's call. A kana or a small kana outside these rows
(`ゎ`/`ヮ`, `ゕ`, `ヷ`, the iteration marks `ゝ` `ヽ`, a small kana not after the listed kana) has no spelling.

**注意事項** (verbatim):

- "撥音：B、M、Pの前の「ん」は、NではなくMで表記します。 例：難波（ナンバ）NAMBA、本間（ホンマ）HOMMA、三瓶（サンペイ）SAMPEI"
  → `ん` is N, M before a syllable whose Latin form starts with B, M or P (also `ゔ` BU).
- "促音：子音を重ねて表記します。 例：服部（ハットリ）HATTORI、吉川（キッカワ）KIKKAWA ただし、チ（CHI）、チャ（CHA）、チュ（CHU）、チョ（CHO）音の前には「T」を表記します。 例：発地（ホッチ）HOTCHI、八丁（ハッチョウ）HATCHO"
  → `っ` doubles the first consonant of the next syllable, T before CH. Before a vowel, `ん`, `っ`, `ー` or at the end
  it has no spelling. Before Y or W (`っや` YYA, `っわ` WWA) the package applies the general "子音を重ねて" rule; MOFA
  gives no example for them.
- "長音：OやUは記入しません。" with the cases:
  - "「―」を省略する場合 例：ニーナ（ニーナ）NINA、シーナ（シーナ）SHINA、サリー（サリー）SARI" → `ー` is omitted.
  - "「イ」を省略しない場合 例：新菜（ニイナ）NIINA、しいな（シイナ）SHIINA、さりい（サリイ）SARII" → `い` is kept.
  - "「ウ」を含む長音「ウウ」の場合（「UU」は表記しません。） 例：日向（ヒュウガ）HYUGA、裕貴（ユウキ）YUKI、優子（ユウコ）YUKO"
  - "「オ」を含む長音「オウ」の場合（「OU」は表記しません。） 例：幸太（コウタ）KOTA、洋子（ヨウコ）YOKO、亮子（リョウコ）RYOKO"
  - "「オ」を含む長音「オオ」の場合（「OO」は表記しません。） 例：大野（オオノ）ONO、大河内（オオコウチ）OKOCHI、大西（オオニシ）ONISHI"
  - "末尾が「オオ」音で、フリガナが「オ」の場合（「OO」と表記します。） 例：妹尾（セノオ）SENOO、高藤（タカトオ）TAKATOO、横尾（ヨコオ）YOKOO"
  - "末尾が「オウ」音で、フリガナが「ウ」の場合（「OU」とは表記しません。） 例：伊藤（イトウ）ITO、高藤（タカトウ）TAKATO、御園生（ミソノウ）MISONO"
  - "「ヴ」のつく氏名例 例：ヴィヴィアン（ヴィヴィアン）BUIBUIAN 又はBIBIAN、 ヴォードレール（ヴォードレール）BUODORERU 又はBODORERU"

  → `う` after a syllable ending in O or U is omitted; `お` after a syllable ending in O is omitted, except as the last
  kana of the reading (SENOO). `ー` after a syllable is omitted; a leading `ー` has no spelling. Other vowel pairs
  (`ああ`, `いい`, `えい`, `うお`) are kept: the rule names only O and U.

**Limitation:** kana cannot tell a long vowel from a morpheme boundary. A non-long-vowel おう/うう/おお across a
morpheme boundary, e.g. 井上 いのうえ INOUE, 大内 おおうち OUCHI, is derived as a long vowel (INOE, OCHI) and needs the
Latin override (accepted under §4). The derivation is kept: the spec requires a derived form, and the reading alone
does not carry the boundary.

Every example above is a test case. The rules for the start of a reading are the package's (MOFA quotes none): a
reading that starts with a small kana, `ん`, `っ` or `ー` has no Hepburn form. Such a name, or one whose Latin form is
too long, is refused without a Latin override with "(no Latin form)" or "(Latin form too long)".

### 4. Override policy: `AnyIcaoSpelling`

【ヘボン式によらないローマ字氏名表記】 (verbatim): "国際結婚や両親のいずれかが外国人、又は二重国籍等により、外国式の名前をヘボン式ローマ字以外の表記で記載することを希望する場合には、その綴りが実際に使用されていることを示す書類（出生証明書、婚姻証明書、配偶者や父母の外国旅券等）の提出をお願いしています。 上記以外でヘボン式によらないローマ字表記（長音「OH」表記等）を希望するには、予めご相談ください。 例：伊藤（イトウ）ITOH、大野（オオノ）OHNO パスポートを一度取得された後の表記の変更については、原則変更できません。"

A passport may carry a spelling outside the table (a foreign spelling on documents, or OH and others after
consultation), so any valid ICAO name is kept as the override (`AnyIcaoSpelling`), as for RU. Nothing checks it
against the readings. 【別名併記】 (a second name in brackets) is not modelled.
