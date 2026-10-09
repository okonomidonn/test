# リール動画のナレーション（Google Cloud Text-to-Speech 用）

動画は8秒。画面の流れに合わせて区切りを入れてある。
- 0〜1.5秒：見出し
- 1.8〜6秒：スマホの求人一覧がスクロール
- 6.3秒〜：ロゴと「登録無料」

設定
- 入力：SSML
- 言語：日本語（ja-JP）
- 声：女性なら `ja-JP-Neural2-B`、男性なら `ja-JP-Neural2-C`（もっと自然な声が選べる場合は Chirp3-HD の声）
- 話す速さ（speakingRate）：1.1。8秒を超えるときは 1.15〜1.2 に上げる
- 出力：MP3

「zaito」は英字のままだと読み方が崩れるので、`<sub alias="ザイト">` で読み方を指定している。

---

## A（子育て中・ブランク）

```xml
<speak>
  出社なしの在宅ワーク、探してみませんか？
  <break time="300ms"/>
  未経験・ブランクOK、時給<say-as interpret-as="cardinal">1300</say-as>円からの求人をまとめました。
  <break time="250ms"/>
  <sub alias="ザイト">zaito</sub>、登録無料です。
</speak>
```

## B（事務経験）

```xml
<speak>
  事務の経験、在宅で活かしませんか？
  <break time="300ms"/>
  経理、秘書、営業事務。出社なしの事務求人をまとめました。
  <break time="250ms"/>
  <sub alias="ザイト">zaito</sub>、登録無料です。
</speak>
```

## C（学生）

```xml
<speak>
  在宅でできるインターン、探してみませんか？
  <break time="300ms"/>
  授業の合間に、自宅から。学生OKの求人をまとめました。
  <break time="250ms"/>
  <sub alias="ザイト">zaito</sub>、登録無料です。
</speak>
```

## 動画と合わせる
できたMP3（と使いたいBGM）を Claude に渡せば、音量をそろえて動画に合わせたmp4を作る。
- ナレーションは0.2秒目から始める
- BGMはナレーションの下で小さめ（−18dBくらい）にし、最後の1秒で消えていくようにする
