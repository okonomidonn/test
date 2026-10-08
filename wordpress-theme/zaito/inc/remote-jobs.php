<?php
/**
 * まとめ求人（一覧はトップページ、詳細は /remote/{slug}/）。
 *
 * 公開されている完全在宅・学生OKの求人を、運営が1件ずつ確認してまとめたもの。
 * zaitoに載せるのは会社名・職種・報酬・稼働条件・在宅の根拠・元のページへのリンクだけで、
 * 募集の本文は写さない。応募は元のページで行う（zaitoは応募を受け付けない・取り次がない）。
 *
 * 「応募ページへ進む」はメールアドレスの登録（先行登録と同じ zaito_interest）が必要。
 * 登録したブラウザには署名つきのCookieを置き、/remote/{slug}/go/ で
 * クリックを記録してから元のページへ転送する。記録した数は「求人 > まとめ求人」で見られ、
 * 企業への営業（「○人の学生が応募ページへ進みました」）に使う。
 *
 * 求人を追加・修正するときは zaito_remote_jobs() を編集する。掲載をやめるときは
 * 'closed' => true にする（URLは残し、「募集終了」と表示する）。
 */

/**
 * まとめ求人の一覧。checked は運営が求人ページを確認した日。
 * caution は「在宅だが書き添えが必要な条件」（出社の可能性など）。
 */
function zaito_remote_jobs() {
    return array(
        array(
            'slug'     => 'kishiho-recruit-marketing',
            'company'  => '岸保産業株式会社',
            'title'    => '採用マーケティング・採用広報のアシスタント',
            'category' => 'マーケティング',
            'summary'  => '業務用厨房用品の専門商社で、Wantedlyを使った採用広報や面接の日程調整を担当します。',
            'remote'   => '完全フルリモート。住んでいる場所は問わない（海外留学中も可）',
            'pay'      => '',
            'hours'    => '',
            'target'   => '学生',
            'url'      => 'https://www.wantedly.com/projects/2234809',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'nhigh-net-course-support',
            'company'  => '学校法人角川ドワンゴ学園',
            'title'    => 'N高グループ ネットコースの生徒サポート',
            'category' => '教育・学習サポート',
            'summary'  => 'N高・S高のネットコースで、生徒とのオンライン面談や質問対応、進路書類のサポートをします。',
            'remote'   => 'フルリモート（自宅にWi-Fi環境が必要）',
            'pay'      => '時給1,300円〜',
            'hours'    => '平日13〜18時のうち週2日以上・1日5時間以上、半年以上',
            'target'   => '大学生・大学院生（全学年）',
            'url'      => 'https://01intern.com/job/5757.html',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'agaroot-learning-coach',
            'company'  => '株式会社アガルート',
            'title'    => '学習コーチングスタッフ',
            'category' => '教育・学習サポート',
            'summary'  => 'オンライン講座の受講生の学習をコーチングで支える仕事です。',
            'remote'   => 'フルリモートで業務可能',
            'pay'      => '',
            'hours'    => '',
            'target'   => '学生歓迎',
            'url'      => 'https://www.wantedly.com/projects/2262541',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'tokihana-sns',
            'company'  => '株式会社トキハナ',
            'title'    => 'ウエディングメディアのSNS運用',
            'category' => 'SNS運用',
            'summary'  => '結婚式場探しのサービス「トキハナ」のInstagramなどのSNS運用を担当します。',
            'remote'   => 'フルリモート（在宅）。往訪や対面の予定がなければ出社不要',
            'pay'      => '',
            'hours'    => '柔軟シフト',
            'target'   => '学生',
            'url'      => 'https://www.wantedly.com/projects/1309735',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'tokihana-seo',
            'company'  => '株式会社トキハナ',
            'title'    => 'Webメディアのマーケター（SEO分析・改善）',
            'category' => 'Webマーケティング',
            'summary'  => '自社メディアの記事のSEO分析と改善を担当します。',
            'remote'   => '原則フルリモート。往訪や対面の予定がなければ在宅',
            'pay'      => '',
            'hours'    => '柔軟シフト',
            'target'   => '学生',
            'url'      => 'https://www.wantedly.com/projects/1324140',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'clearnote-marketing',
            'company'  => '株式会社CLEARNOTE',
            'title'    => '学生向け学習アプリのマーケティング',
            'category' => 'マーケティング',
            'summary'  => '学生向けの学習ノートアプリ「Clearnote」のマーケティングを担当します。',
            'remote'   => 'リモートワーク（在宅勤務）',
            'pay'      => '有給（金額は募集ページで確認）',
            'hours'    => '週3日以上（土日も可）',
            'target'   => '大学生（1・2年生歓迎）',
            'url'      => 'https://www.wantedly.com/projects/104947',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'donuts-ray-writer',
            'company'  => '株式会社DONUTS',
            'title'    => '女性向けメディア「Ray」の記事制作',
            'category' => 'ライティング',
            'summary'  => '女性向けファッション誌「Ray」のWebメディアで、記事の制作を担当します。',
            'remote'   => '完全リモート',
            'pay'      => '1記事300〜1,200円（成果報酬）',
            'hours'    => '週2日・1日3時間から、6か月以上',
            'target'   => '大学1・2年生（学生限定）',
            'url'      => 'https://www.in-fra.jp/long-internships/11734',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'yoake-sns-marketing',
            'company'  => 'yoake株式会社',
            'title'    => '就活支援サービスのSNSマーケティング',
            'category' => 'SNS運用',
            'summary'  => 'AIを使った就活支援サービスのSNSマーケティングを担当します。',
            'remote'   => '完全リモート',
            'pay'      => '',
            'hours'    => '時間は柔軟、3か月以上',
            'target'   => '学生（28・29卒歓迎）',
            'url'      => 'https://www.wantedly.com/projects/2525043',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'stocksun-ai-marketing',
            'company'  => 'StockSun株式会社',
            'title'    => 'AI・広告運用・Webデザインのマーケティング',
            'category' => 'Webマーケティング',
            'summary'  => 'ChatGPTなどのAIを使った広告運用やWebデザインなど、Webマーケティングの実務を担当します。',
            'remote'   => '完全フルリモート。地方在住OK',
            'pay'      => '',
            'hours'    => '週5時間から相談可',
            'target'   => '大学生・大学院生',
            'url'      => 'https://www.wantedly.com/projects/2056107',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'aitem-sns-marketing',
            'company'  => '株式会社R（英会話スクールAitem）',
            'title'    => '英会話スクールのWeb・SNSマーケティング',
            'category' => 'SNS運用',
            'summary'  => 'オンライン英会話スクールのWebとSNSのマーケティングを担当します。',
            'remote'   => '完全在宅で勤務可能',
            'pay'      => '',
            'hours'    => '週20時間以上、7か月以上',
            'target'   => '学生・社会人',
            'url'      => 'https://www.wantedly.com/projects/624928',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'norinori-sns',
            'company'  => '株式会社nori・nori',
            'title'    => 'モビリティベンチャーのSNS運用',
            'category' => 'SNS運用',
            'summary'  => '貸切バスをみんなで使うサービスを運営するベンチャーで、SNSでの発信を担当します。',
            'remote'   => 'フルリモート（本社は神奈川）。Slack・Zoomでやりとり',
            'pay'      => '',
            'hours'    => '',
            'target'   => '学生',
            'url'      => 'https://www.wantedly.com/projects/2542638',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'anysense-app-ai',
            'company'  => '株式会社エニセンス',
            'title'    => 'アプリ開発・デザイン・生成AI',
            'category' => 'エンジニア・デザイン',
            'summary'  => 'アプリ作成サービス「myApp」の開発、UI・UXデザイン、生成AIの導入に関わります。',
            'remote'   => '完全在宅で勤務可能。地方在住OK',
            'pay'      => '',
            'hours'    => '週20時間以上、7か月以上',
            'target'   => '学生・社会人',
            'url'      => 'https://www.wantedly.com/projects/1977408',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'reapra-consulting-marketing',
            'company'  => 'Reapra Japan',
            'title'    => 'コンサルティング×マーケティング',
            'category' => 'コンサル・マーケティング',
            'summary'  => '起業家を育てるベンチャービルダーで、コンサルティングとマーケティングを実務で学びます。',
            'remote'   => 'フルリモートが前提',
            'pay'      => '',
            'hours'    => '',
            'target'   => '学生（29卒向け）',
            'url'      => 'https://www.wantedly.com/projects/2390659',
            'checked'  => '2026-10-08',
            'caution'  => '福岡での合宿が定期的にあります。',
        ),
        array(
            'slug'     => 'iflag-ai-agent',
            'company'  => '株式会社アイフラッグ',
            'title'    => 'AIエージェント開発のエンジニア',
            'category' => 'エンジニア',
            'summary'  => 'AIエージェントの開発プロジェクトに、エンジニアとして参加します（Python中心）。',
            'remote'   => 'フルリモート。大阪オフィスへの出社は希望者のみ',
            'pay'      => '時給2,000〜2,500円',
            'hours'    => '週3日以上・平日1日4時間以上',
            'target'   => '大学生・大学院生（チーム開発の経験が必要）',
            'url'      => 'https://01intern.com/job/7813.html',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'emuni-ai-engineer',
            'company'  => '株式会社エムニ',
            'title'    => 'AIエンジニア',
            'category' => 'エンジニア',
            'summary'  => '松尾研・京大発のAIスタートアップで、AIを使った開発を担当します。',
            'remote'   => '基本はフルリモート（出社も可能）',
            'pay'      => '時給1,500〜2,500円',
            'hours'    => '週12時間以上（週3日・1日2時間から）',
            'target'   => '大学生・大学院生（PythonかTypeScriptの経験）',
            'url'      => 'https://01intern.com/job/5386.html',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'jurin-ai-strategy',
            'company'  => '樹林AI株式会社',
            'title'    => '経営戦略サポート（経営陣のアシスタント）',
            'category' => '事務・アシスタント',
            'summary'  => '経営陣の直下で、資料作成や市場調査などの経営戦略のサポートをします。',
            'remote'   => '基本はフルリモート',
            'pay'      => '時給1,300〜3,000円',
            'hours'    => '週3日以上・1日3時間以上',
            'target'   => '学生・既卒・社会人',
            'url'      => 'https://01intern.com/job/6811.html',
            'checked'  => '2026-10-08',
            'caution'  => '「基本は」フルリモートのため、出社がまったくないかは応募前に確認してください。',
        ),
        array(
            'slug'     => 'jxpress-news-editor',
            'company'  => '株式会社JX通信社',
            'title'    => 'ニュース速報の編集',
            'category' => '編集・ライティング',
            'summary'  => 'ニュース速報サービスで、速報の編集に関わります。',
            'remote'   => 'リモート中心。地方在住も歓迎',
            'pay'      => '時給1,500円〜',
            'hours'    => '週3日・週12時間以上、3〜4年続けられる方',
            'target'   => '大学1・2年生',
            'url'      => 'https://01intern.com/job/6976.html',
            'checked'  => '2026-10-08',
            'caution'  => '1都3県に住んでいる方は、最初の研修期間の出社が推奨されています。',
        ),
        array(
            'slug'     => 'scien-ai-project',
            'company'  => '株式会社SCIEN',
            'title'    => '企業のAIプロジェクト推進',
            'category' => '事務・アシスタント',
            'summary'  => '東大松尾研発のスタートアップで、企業のAIプロジェクトの議事録・資料作成・進捗管理を担当します。',
            'remote'   => 'フルリモート/完全在宅OK',
            'pay'      => '時給1,500円〜',
            'hours'    => '週4日以上・週20時間以上',
            'target'   => '全学年',
            'url'      => 'https://01intern.com/job/6943.html',
            'checked'  => '2026-10-08',
            'caution'  => '求人ページに「一部リモート」の記載と通勤手当もあるため、出社の有無は応募前に確認してください。',
        ),
        array(
            'slug'     => 'copernix-space-sns',
            'company'  => '株式会社CoperniX',
            'title'    => '宇宙メディアのSNSマーケティング',
            'category' => 'SNS運用',
            'summary'  => '宇宙を扱うメディアで、Instagram・TikTok・YouTubeショートの企画・編集・分析を担当します。',
            'remote'   => '基本リモート',
            'pay'      => '担当コンテンツの広告収益の15〜20%（成果報酬）',
            'hours'    => '週3日・1日3時間から、6か月以上',
            'target'   => '学生',
            'url'      => 'https://www.in-fra.jp/long-internships/29047',
            'checked'  => '2026-10-08',
            'caution'  => '撮影やイベントのときだけ現地（埼玉県坂戸市）での参加があります。',
        ),
        array(
            'slug'     => 'caster-my-assistant',
            'company'  => '株式会社キャスター',
            'title'    => 'オンラインアシスタント（My Assistant担当）',
            'category' => '事務・アシスタント',
            'summary'  => 'クライアントとチャットでやりとりしながら、日程調整・経理や労務の補助・SNS運用などのバックオフィス業務をサポートします。',
            'remote'   => 'フルリモート。国内外どこでも可',
            'pay'      => '時給1,000円',
            'hours'    => '週20時間程度（平日9〜17時中心、分割稼働も可）',
            'target'   => '社会人（事務経験のある方）',
            'url'      => 'https://herp.careers/v1/caster/2W3I-8SlQuLn',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'caster-spot-assistant',
            'company'  => '株式会社キャスター',
            'title'    => 'スポットアシスタント',
            'category' => '事務・アシスタント',
            'summary'  => '資料作成・スケジュール管理・経理・採用業務など、企業のバックオフィス業務を依頼ごとに担当します。',
            'remote'   => '完全リモートワーク。国内外どこでも可',
            'pay'      => '案件ごと（例：月50時間で55,000円）',
            'hours'    => '稼働時間は自由。平日9〜18時に連絡がとれること',
            'target'   => '社会人経験1年以上・事務経験のある方',
            'url'      => 'https://herp.careers/v1/caster/BF7o6cbuRriP',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'knit-dedicated-staff',
            'company'  => '株式会社ニット（HELP YOU）',
            'title'    => '企業専任のオンラインサポートスタッフ',
            'category' => '事務・アシスタント',
            'summary'  => '1社の担当として、営業事務・採用・カスタマーサポート・経理などの業務を、依頼の整理から実務まで受け持ちます。',
            'remote'   => '100%リモートワーク',
            'pay'      => '時給1,500円（研修中1,350円）',
            'hours'    => '平日週3日以上・9〜18時のうち1日2〜3時間',
            'target'   => '社会人（事務・バックオフィス経験3年以上）',
            'url'      => 'https://herp.careers/v1/knithy202303/JaA0imqhrpFw',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'knit-online-secretary',
            'company'  => '株式会社ニット（HELP YOU）',
            'title'    => 'オンライン秘書',
            'category' => '事務・アシスタント',
            'summary'  => '担当する経営者の日程調整・出張手配・会議資料の作成など、秘書業務をオンラインで行います。',
            'remote'   => '100%リモートワーク',
            'pay'      => '時給1,500円（研修中1,350円）',
            'hours'    => '平日週3日以上・9〜18時のうち1日2〜3時間',
            'target'   => '社会人（秘書などの経験1年以上・社会人経験3年以上）',
            'url'      => 'https://herp.careers/v1/knithy202303/NmVmLfWEJpZf',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'wonder-education-support',
            'company'  => '株式会社Wonder Education',
            'title'    => 'オンライン講座のお客様対応（LINE・メール）',
            'category' => '事務・カスタマーサポート',
            'summary'  => 'オンラインの学びのサービスで、受講者からのLINE・メールの問い合わせ対応や利用案内、数値の入力を担当します。',
            'remote'   => '完全在宅',
            'pay'      => '',
            'hours'    => '週4日以上（平日のみOK）・8〜24時のうち1日5時間以上',
            'target'   => '社会人経験1年以上',
            'url'      => 'https://www.wantedly.com/projects/2545514',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'funtre-web-assistant',
            'company'  => 'FunTre株式会社',
            'title'    => 'Webマーケティングのアシスタント',
            'category' => 'マーケティング',
            'summary'  => 'マーケティングのコンサルタントのもとで、日程管理・SNS運用の補助・ライティング・市場調査などを担当します。',
            'remote'   => '完全在宅',
            'pay'      => '',
            'hours'    => '週15時間程度以上（案件による）',
            'target'   => '社会人・主婦（未経験・ブランク・子育て中も歓迎）',
            'url'      => 'https://www.wantedly.com/projects/2166709',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'albona-beauty-sns',
            'company'  => '株式会社ALBONA',
            'title'    => '美容系SNSクリエイター',
            'category' => 'SNS運用',
            'summary'  => 'コスメ・美容商品のX投稿の文章づくりや、Canvaでの画像作成、トレンド調査を担当します。',
            'remote'   => '完全在宅・フルリモート',
            'pay'      => '',
            'hours'    => '時間は自由（納期を守る形）',
            'target'   => '未経験・初心者も歓迎',
            'url'      => 'https://www.wantedly.com/projects/2564978',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'highball-sns-marketer',
            'company'  => '株式会社ハイボール',
            'title'    => '縦型ショートドラマのSNSマーケター',
            'category' => 'SNS運用',
            'summary'  => 'ショートドラマアプリのチャンネル企画・編集、クリエイターとのやりとり、SNS投稿と運用を担当します。',
            'remote'   => 'フルリモート・完全在宅。全国どこからでも可',
            'pay'      => '',
            'hours'    => '平日10〜19時・週5日（フルタイム）',
            'target'   => '未経験歓迎（学生は休学費用の補助あり）',
            'url'      => 'https://www.wantedly.com/projects/2169549',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'xcuu-thumbnail-designer',
            'company'  => '株式会社Xcuu',
            'title'    => 'YouTubeサムネイルのデザイナー',
            'category' => 'デザイン',
            'summary'  => '企業のYouTubeチャンネルのサムネイル画像を、クリックされやすいレイアウトを考えながら制作します。',
            'remote'   => 'フルリモート',
            'pay'      => '',
            'hours'    => '週数時間から相談可',
            'target'   => '副業・フリーランス歓迎',
            'url'      => 'https://www.wantedly.com/projects/2589775',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'ndpromotion-ai-video',
            'company'  => '株式会社N.D.Promotion',
            'title'    => '生成AIを使ったショート動画の編集',
            'category' => '動画制作',
            'summary'  => '生成AIで素材を作り、TikTokやInstagram向けのキャラクターの短い動画を企画・編集します。',
            'remote'   => 'フルリモート',
            'pay'      => '',
            'hours'    => '週20時間以上・1日4時間以上（自由シフト、土日祝も可）',
            'target'   => '経験は問わない（家庭の事情での急な休みにも対応）',
            'url'      => 'https://www.wantedly.com/projects/2399992',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'anymind-video-localize',
            'company'  => 'AnyMind Group',
            'title'    => '英語圏向けショート動画の編集・ローカライズ',
            'category' => '動画制作',
            'summary'  => '日本のトレンドを海外に届けるため、会話形式のショート動画を英語圏向けに編集・演出します。',
            'remote'   => 'フルリモート（完全在宅）',
            'pay'      => '',
            'hours'    => '週2日以上・平日10〜18時に連絡がとれること',
            'target'   => '動画編集ができる方',
            'url'      => 'https://www.wantedly.com/projects/2450817',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'soel-sns-designer',
            'company'  => 'SOEL株式会社',
            'title'    => 'ホテル・飲食店のInstagram投稿デザイン',
            'category' => 'デザイン',
            'summary'  => 'ホテルや飲食店の公式Instagramの投稿画像を、画像加工・文章・イラストまで含めて制作します。',
            'remote'   => 'フルリモート。住む場所は問わない',
            'pay'      => '',
            'hours'    => '記載なし（海外在住なら日本時間10〜19時に連絡がとれること）',
            'target'   => 'フリーランス（Photoshop・Illustratorの実務1年以上）',
            'url'      => 'https://www.wantedly.com/projects/2527967',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'freeks-beauty-writer',
            'company'  => 'freeks株式会社',
            'title'    => 'ファッション・美容メディアのライター',
            'category' => 'ライティング',
            'summary'  => 'ファッション・美容・コスメのWeb記事やSNS投稿文、SEO記事の執筆と改善を担当します。',
            'remote'   => 'フルリモート（希望すれば出社も可）',
            'pay'      => '',
            'hours'    => '記載なし',
            'target'   => '副業・フリーランス歓迎',
            'url'      => 'https://www.wantedly.com/projects/2263544',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'forcer-amazon-assistant',
            'company'  => 'FORCE-R株式会社',
            'title'    => 'Amazonストア運用のアシスタント',
            'category' => '事務・アシスタント',
            'summary'  => 'Amazonの管理画面で、商品登録・セールの入稿・在庫確認などの運用をサポートします。',
            'remote'   => '在宅勤務（場所は問わない）',
            'pay'      => '',
            'hours'    => '平日のみ（土日祝休み）',
            'target'   => 'Amazonでの実務経験がある方',
            'url'      => 'https://www.wantedly.com/projects/2275775',
            'checked'  => '2026-10-08',
            'caution'  => '最初の1か月は出社が推奨されています。',
        ),
    );
}

function zaito_remote_job( $slug ) {
    foreach ( zaito_remote_jobs() as $job ) {
        if ( $job['slug'] === $slug ) {
            return $job;
        }
    }
    return null;
}

/**
 * 一覧はトップページ（/）。詳細は /remote/{slug}/。
 */
function zaito_remote_url( $slug = '' ) {
    return $slug ? home_url( '/remote/' . $slug . '/' ) : home_url( '/' );
}

/* ---------- URL ---------- */

function zaito_remote_rewrite_rules() {
    add_rewrite_rule( '^remote/?$', 'index.php?zaito_page=remote', 'top' );
    add_rewrite_rule( '^remote/([a-z0-9-]+)/?$', 'index.php?zaito_page=remote&zaito_remote=$matches[1]', 'top' );
    add_rewrite_rule( '^remote/([a-z0-9-]+)/go/?$', 'index.php?zaito_page=remote&zaito_remote=$matches[1]&zaito_go=1', 'top' );
}
add_action( 'init', 'zaito_remote_rewrite_rules' );

function zaito_remote_query_vars( $vars ) {
    $vars[] = 'zaito_remote';
    $vars[] = 'zaito_go';
    return $vars;
}
add_filter( 'query_vars', 'zaito_remote_query_vars' );

/**
 * /remote/ 以下を表示する。公開前モードでもログイン状態に関係なく表示する。
 */
function zaito_remote_template_redirect() {
    if ( 'remote' !== get_query_var( 'zaito_page' ) ) {
        return;
    }
    $slug = (string) get_query_var( 'zaito_remote' );

    // 条件別の一覧（/zaitaku/{slug}/）。求人が少なすぎる条件はトップページへ。
    $cond = (string) get_query_var( 'zaito_cond' );
    if ( '' !== $cond ) {
        $active = zaito_remote_active_conditions();
        if ( ! isset( $active[ $cond ] ) ) {
            wp_safe_redirect( home_url( '/' ), 302 );
            exit;
        }
        $zaito_list_cond = $cond;
        status_header( 200 );
        header( 'Content-Type: text/html; charset=UTF-8' );
        include get_template_directory() . '/lp/remote-list.php';
        exit;
    }

    // 一覧はトップページに移したため、/remote/ はトップページへ転送する。
    if ( '' === $slug ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }

    $job = zaito_remote_job( $slug );
    if ( ! $job ) {
        wp_safe_redirect( zaito_remote_url(), 302 );
        exit;
    }

    if ( get_query_var( 'zaito_go' ) ) {
        zaito_remote_go( $job );
        exit;
    }

    $zaito_remote_job = $job;
    status_header( 200 );
    header( 'Content-Type: text/html; charset=UTF-8' );
    include get_template_directory() . '/lp/remote-job.php';
    exit;
}
// 公開前モードの転送（priority 0）より先に判定する。
add_action( 'template_redirect', 'zaito_remote_template_redirect', -1 );

/* ---------- 登録済みブラウザの判定 ---------- */

/**
 * 登録したブラウザに置くCookie。中身は「登録ID.署名」で、署名がないと別人のIDを名乗れない。
 * zaito_m は「登録済み」の目印だけのCookieで、ページのJSが登録フォームを出すかの判定に使う
 * （ページがキャッシュされても正しく動くよう、判定はブラウザ側で行う）。
 */
function zaito_remote_member_sign( $interest_id ) {
    return hash_hmac( 'sha256', 'zaito_member|' . (int) $interest_id, wp_salt( 'auth' ) );
}

function zaito_remote_set_member_cookie( $interest_id ) {
    $expires = time() + YEAR_IN_SECONDS;
    $opts    = array(
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax',
    );
    setcookie( 'zaito_member', (int) $interest_id . '.' . zaito_remote_member_sign( $interest_id ), $opts );
    $opts['httponly'] = false;
    setcookie( 'zaito_m', '1', $opts );
}

function zaito_remote_member_id() {
    $raw = isset( $_COOKIE['zaito_member'] ) ? (string) wp_unslash( $_COOKIE['zaito_member'] ) : '';
    if ( ! preg_match( '/^(\d+)\.([a-f0-9]{64})$/', $raw, $m ) ) {
        return 0;
    }
    if ( ! hash_equals( zaito_remote_member_sign( $m[1] ), $m[2] ) ) {
        return 0;
    }
    return 'zaito_interest' === get_post_type( (int) $m[1] ) ? (int) $m[1] : 0;
}

/* ---------- 応募ページへの転送とクリックの記録 ---------- */

function zaito_remote_go( $job ) {
    nocache_headers();
    $member = zaito_remote_member_id();
    if ( ! $member ) {
        wp_safe_redirect( zaito_remote_url( $job['slug'] ) . '#apply', 302 );
        return;
    }
    if ( empty( $job['closed'] ) ) {
        zaito_remote_record_click( $job['slug'], $member );
    }
    // 転送先は zaito_remote_jobs() に運営が書いたURLだけ（利用者の入力は使わない）。
    wp_redirect( esc_url_raw( $job['url'] ), 302 );
}

/**
 * 求人ごとに「応募ページへ進んだ回数」と「進んだ学生（登録ID）」を記録する。
 * 学生側にも、どの求人に進んだかを残す（新着のお知らせを興味に合わせるため）。
 */
function zaito_remote_record_click( $slug, $member ) {
    $stats = get_option( 'zaito_remote_stats', array() );
    if ( ! is_array( $stats ) ) {
        $stats = array();
    }
    if ( ! isset( $stats[ $slug ] ) ) {
        $stats[ $slug ] = array( 'clicks' => 0, 'members' => array(), 'last' => '' );
    }
    $stats[ $slug ]['clicks']++;
    $stats[ $slug ]['members'][ $member ] = current_time( 'Y-m-d' );
    $stats[ $slug ]['last']                = current_time( 'Y-m-d H:i' );
    update_option( 'zaito_remote_stats', $stats, false );

    $clicked = get_post_meta( $member, 'remote_clicks', true );
    $clicked = is_array( $clicked ) ? $clicked : array();
    $clicked[ $slug ] = current_time( 'Y-m-d' );
    update_post_meta( $member, 'remote_clicks', $clicked );
}

/* ---------- 登録 ---------- */

/**
 * まとめ求人の詳細ページの登録フォーム。先行登録と同じ zaito_interest に登録し
 * （登録済みなら同じレコードを使う）、このブラウザを登録済みにする。
 */
function zaito_handle_remote_register() {
    $slug = isset( $_POST['job'] ) ? sanitize_key( wp_unslash( $_POST['job'] ) ) : '';
    $job  = $slug ? zaito_remote_job( $slug ) : null;
    $go   = $job ? zaito_remote_url( $job['slug'] ) . 'go/' : zaito_remote_url();
    $back = $job ? zaito_remote_url( $job['slug'] ) : zaito_remote_url();
    $json = false !== strpos( isset( $_SERVER['HTTP_ACCEPT'] ) ? $_SERVER['HTTP_ACCEPT'] : '', 'application/json' );

    $fail = function ( $message ) use ( $json, $back ) {
        if ( $json ) {
            wp_send_json_error( array( 'message' => $message ), 400 );
        }
        wp_safe_redirect( add_query_arg( 'signup', 'error', $back ) . '#apply' );
        exit;
    };

    // ハニーポット: ボットには成功したように見せて何も保存しない。
    if ( ! empty( $_POST['website'] ) ) {
        $json ? wp_send_json_success( array( 'go' => $back ) ) : wp_safe_redirect( $back );
        exit;
    }

    $email = isset( $_POST['email'] ) ? strtolower( trim( sanitize_email( wp_unslash( $_POST['email'] ) ) ) ) : '';
    if ( ! $email || ! is_email( $email ) ) {
        $fail( '正しいメールアドレスを入力してください' );
    }
    if ( zaito_lp_rate_limited( 'remote' ) ) {
        $fail( '送信回数が多すぎます。しばらくしてからお試しください' );
    }

    // 登録元: 求人詳細の応募ボタンからなら remote、トップページの新着登録からなら top。
    $result = zaito_lp_find_or_create_interest( $email, $job ? 'remote' : 'top' );
    if ( ! $result ) {
        $fail( '登録に失敗しました。時間をおいて再度お試しください' );
    }
    list( $interest_id, $created ) = $result;
    if ( $created ) {
        zaito_lp_send_waitlist_thanks( $email, 'remote' );
    }
    zaito_remote_set_member_cookie( $interest_id );

    if ( $json ) {
        wp_send_json_success( array( 'go' => $go, 'created' => $created ) );
    }
    wp_redirect( $go );
    exit;
}
add_action( 'wp_ajax_zaito_remote_register', 'zaito_handle_remote_register' );
add_action( 'wp_ajax_nopriv_zaito_remote_register', 'zaito_handle_remote_register' );

/* ---------- 登録後のプロフィール（任意） ---------- */

/**
 * 登録の直後に聞く任意の項目。どれも押すだけで答えられる選択肢にする（学校名だけ入力）。
 */
function zaito_remote_profile_options() {
    return array(
        'role'      => array( '大学生', '大学院生', '専門学校・短大生', '高校生', '社会人', '主婦・主夫', 'その他' ),
        'grade'     => array( '1年', '2年', '3年', '4年以上' ),
        'interests' => array( 'SNS運用', 'Webマーケティング', 'ライティング・編集', '動画編集', 'デザイン', 'エンジニア', '事務・アシスタント', '教育・学習サポート', 'カスタマーサポート', 'リサーチ・データ入力' ),
    );
}

/**
 * プロフィールの保存。登録済みのCookieがあるブラウザだけが、その人の登録に書き込める。
 */
function zaito_handle_remote_profile() {
    $member = zaito_remote_member_id();
    if ( ! $member ) {
        wp_send_json_error( array( 'message' => '登録情報が確認できませんでした' ), 400 );
    }
    if ( zaito_lp_rate_limited( 'remote_profile' ) ) {
        wp_send_json_error( array( 'message' => '送信回数が多すぎます。しばらくしてからお試しください' ), 400 );
    }
    $opts = zaito_remote_profile_options();

    $role = isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '';
    if ( in_array( $role, $opts['role'], true ) ) {
        update_post_meta( $member, 'role', $role );
    }
    $grade = isset( $_POST['grade'] ) ? sanitize_text_field( wp_unslash( $_POST['grade'] ) ) : '';
    if ( in_array( $grade, $opts['grade'], true ) ) {
        update_post_meta( $member, 'grade', $grade );
    }
    $school = isset( $_POST['school'] ) ? sanitize_text_field( wp_unslash( $_POST['school'] ) ) : '';
    if ( '' !== $school ) {
        update_post_meta( $member, 'school', mb_substr( $school, 0, 80 ) );
    }
    $picked = isset( $_POST['interests'] ) && is_array( $_POST['interests'] )
        ? array_values( array_intersect( array_map( 'sanitize_text_field', wp_unslash( $_POST['interests'] ) ), $opts['interests'] ) )
        : array();
    if ( $picked ) {
        // 先行登録（トップページ）で答えた「興味のある仕事」があれば残して足す。
        $current = get_post_meta( $member, 'interests', true );
        $current = is_array( $current ) ? $current : array();
        update_post_meta( $member, 'interests', array_slice( array_values( array_unique( array_merge( $current, $picked ) ) ), 0, 20 ) );
    }
    wp_send_json_success( array() );
}
add_action( 'wp_ajax_zaito_remote_profile', 'zaito_handle_remote_profile' );
add_action( 'wp_ajax_nopriv_zaito_remote_profile', 'zaito_handle_remote_profile' );

/* ---------- 管理画面「求人 > まとめ求人」 ---------- */

function zaito_remote_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=job_listing',
        'まとめ求人',
        'まとめ求人',
        'edit_posts',
        'zaito-remote-jobs',
        'zaito_render_remote_admin_page'
    );
}
add_action( 'admin_menu', 'zaito_remote_admin_menu' );

function zaito_render_remote_admin_page() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'この画面を表示する権限がありません。' );
    }
    $stats = get_option( 'zaito_remote_stats', array() );
    $stats = is_array( $stats ) ? $stats : array();
    $members = (int) wp_count_posts( 'zaito_interest' )->private;
    ?>
    <div class="wrap">
      <h1>まとめ求人</h1>
      <p>公開ページ: <a href="<?php echo esc_url( zaito_remote_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( zaito_remote_url() ); ?></a>　｜　登録している学生（先行登録を含む）: <strong><?php echo esc_html( $members ); ?>人</strong></p>
      <p>「進んだ学生」は、登録して元の応募ページへ進んだ学生の人数です（同じ学生が何度押しても1人）。企業への営業では、この人数を伝えます。求人の追加・修正は <code>inc/remote-jobs.php</code> で行います。</p>
      <table class="widefat striped">
        <thead><tr><th>企業・求人</th><th style="width:110px">進んだ学生</th><th style="width:110px">クリック数</th><th style="width:150px">最後のクリック</th><th style="width:110px">確認日</th><th>元のページ</th></tr></thead>
        <tbody>
        <?php foreach ( zaito_remote_jobs() as $job ) :
            $s = isset( $stats[ $job['slug'] ] ) ? $stats[ $job['slug'] ] : array( 'clicks' => 0, 'members' => array(), 'last' => '' );
        ?>
          <tr>
            <td><strong><?php echo esc_html( $job['company'] ); ?></strong><br><a href="<?php echo esc_url( zaito_remote_url( $job['slug'] ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $job['title'] ); ?></a><?php echo ! empty( $job['closed'] ) ? '（募集終了）' : ''; ?></td>
            <td><strong style="font-size:16px"><?php echo esc_html( count( $s['members'] ) ); ?></strong>人</td>
            <td><?php echo esc_html( (int) $s['clicks'] ); ?>回</td>
            <td><?php echo esc_html( $s['last'] ? $s['last'] : '―' ); ?></td>
            <td><?php echo esc_html( $job['checked'] ); ?></td>
            <td><a href="<?php echo esc_url( $job['url'] ); ?>" target="_blank" rel="noopener noreferrer">開く</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
}
