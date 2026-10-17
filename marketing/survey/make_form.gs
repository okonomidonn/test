// 在宅ワークについてのアンケートを Google フォームで作るスクリプト。
// 使い方: https://script.google.com/ →「新しいプロジェクト」→ この中身を全部貼り付け → 上の「実行」。
// 初回は許可の画面が出る（「詳細」→「（安全ではないページ）に移動」→「許可」）。
// 実行が終わると、下の「実行ログ」に回答用URLと編集用URLが出る。
function makeSurvey() {
  const form = FormApp.create('在宅ワークについてのアンケート');
  form.setDescription(
    'アルバイトや在宅ワークについて、どう考えているかを教えてください。\n' +
    '2〜3分で終わります。名前やメールアドレスは聞きません。\n' +
    '回答は、在宅の仕事を探しやすくするための参考にだけ使います。'
  );
  form.setProgressBar(true);
  form.setConfirmationMessage('ご回答ありがとうございました。');

  // 1ページ目: 職業（学生だけ学校の質問へ進む）
  const job = form.addMultipleChoiceItem().setTitle('現在の職業を教えてください').setRequired(true);

  // 2ページ目: 学生だけ
  const pSchool = form.addPageBreakItem().setTitle('学校について');
  form.addMultipleChoiceItem().setTitle('学年').setRequired(true)
    .setChoiceValues(['1年生', '2年生', '3年生', '4年生', '5年生以上', '修士課程', '博士課程']);
  form.addMultipleChoiceItem().setTitle('文系・理系').setRequired(true)
    .setChoiceValues(['文系', '理系', 'どちらともいえない']);

  // 3ページ目: いま働いているか（していない人はバイトの中身を飛ばす）
  const pWork = form.addPageBreakItem().setTitle('アルバイト・お仕事について');
  const working = form.addMultipleChoiceItem().setTitle('いま、アルバイトをしていますか').setRequired(true);

  // 4ページ目: いまのバイト
  const pNow = form.addPageBreakItem().setTitle('いまのアルバイト');
  form.addCheckboxItem().setTitle('どんなアルバイトをしていますか（いくつでも）').setRequired(true)
    .setChoiceValues(['飲食（カフェ・レストランなど）', '小売・コンビニ', '塾講師・家庭教師', '長期インターン',
      '事務・受付', 'イベント・単発', 'コールセンター', '在宅ワーク'])
    .showOtherOption(true);
  form.addMultipleChoiceItem().setTitle('いまの時給（だいたいで大丈夫です）')
    .setChoiceValues(['1,000円未満', '1,000〜1,199円', '1,200〜1,499円', '1,500〜1,999円', '2,000円以上']);

  // 5ページ目: 全員
  const pAll = form.addPageBreakItem().setTitle('バイト選びと在宅ワーク');
  form.addMultipleChoiceItem().setTitle('バイト選びで一番重視するのは何ですか').setRequired(true)
    .setChoiceValues(['時給', '立地・通勤時間', 'シフトの自由さ', '職場の雰囲気・人間関係', '仕事内容・身につくスキル',
      '就活・キャリアに役立つか'])
    .showOtherOption(true);
  form.addMultipleChoiceItem().setTitle('在宅ワーク（家でできる仕事）に興味はありますか').setRequired(true)
    .setChoiceValues(['とてもある', '少しある', 'あまりない', 'まったくない', 'すでに在宅ワークをしている']);
  const services = ['クラウドワークス', 'ランサーズ', 'ココナラ', 'シュフティ', 'ママワークス', 'Wantedly',
    'Indeed', 'タウンワーク', 'バイトル', 'マイナビバイト', 'タイミー', 'コトナル', 'zaito'];
  form.addCheckboxItem().setTitle('知っているサービスはありますか（いくつでも）')
    .setHelpText('名前を聞いたことがあれば選んでください')
    .setChoiceValues(services.concat(['どれも知らない']))
    .showOtherOption(true);
  form.addCheckboxItem().setTitle('使ったことがあるサービスはありますか（いくつでも）')
    .setChoiceValues(services.concat(['どれも使ったことがない']))
    .showOtherOption(true);
  form.addCheckboxItem().setTitle('在宅ワークをしていない理由に近いものを選んでください（いくつでも）')
    .setHelpText('すでに在宅ワークをしている人は、とばして大丈夫です')
    .setChoiceValues(['どこで探せばいいかわからない', '怪しい・詐欺が心配', 'スキルや経験が足りない気がする',
      '未経験OKの仕事が少なそう', '時給が低そう', '出社や研修があるものが多い', '家だと集中できない',
      '一人だと続かなそう・質問しにくい', '人と会って働くほうが好き', 'パソコンを持っていない', '特に理由はない'])
    .showOtherOption(true);
  form.addCheckboxItem().setTitle('在宅でやってみたい仕事はありますか（いくつでも）')
    .setChoiceValues(['事務・データ入力', 'SNS運用', 'ライター・編集', 'デザイン・動画編集', 'プログラミング',
      'カスタマーサポート', 'オンライン講師・学習サポート', 'リサーチ・アンケート', '特にない'])
    .showOtherOption(true);
  form.addMultipleChoiceItem().setTitle('在宅ワークをするなら、週にどのくらい働きたいですか')
    .setChoiceValues(['週5時間未満', '週5〜10時間', '週10〜20時間', '週20時間以上', 'わからない']);
  form.addParagraphTextItem().setTitle('在宅ワークについて、思っていることがあれば自由に書いてください');

  // 分かれ道の設定（ページを作ったあとでないと設定できない）
  const toSchool = ['大学生', '大学院生', '短大・専門学校生', '高校生'];
  const others = ['会社員・公務員', '契約社員・派遣社員', 'パート・アルバイト（学生以外）', 'フリーランス・自営業',
    '主婦・主夫', '求職中', 'その他'];
  job.setChoices(toSchool.map(v => job.createChoice(v, pSchool)).concat(others.map(v => job.createChoice(v, pWork))));
  working.setChoices([working.createChoice('している', pNow), working.createChoice('していない', pAll)]);

  // 回答をスプレッドシートにも貯める
  const ss = SpreadsheetApp.create('在宅ワークについてのアンケート（回答）');
  form.setDestination(FormApp.DestinationType.SPREADSHEET, ss.getId());

  Logger.log('回答用URL（配るのはこれ）: ' + form.shortenFormUrl(form.getPublishedUrl()));
  Logger.log('編集用URL: ' + form.getEditUrl());
  Logger.log('回答のスプレッドシート: ' + ss.getUrl());
}
