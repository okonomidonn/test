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
 * caution は「在宅だが書き添えが必要な条件」（成果報酬・居住地の制限など）。
 * contract は募集ページで確かめた契約の形（分からない求人には書かない）。
 * listed はzaitoに最初に載せた日（確認し直しても変えない）。checked は運営が最後に募集ページを確認した日。
 */
function zaito_remote_jobs() {
    $jobs = array(
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
            'contract' => 'インターン',
            'url'      => 'https://www.wantedly.com/projects/2234809',
            'listed'   => '2026-10-08',
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
            'contract' => 'インターン（雇用契約）',
            'url'      => 'https://01intern.com/job/5757.html',
            'listed'   => '2026-10-08',
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
            'contract' => 'インターン・アルバイト',
            'url'      => 'https://www.wantedly.com/projects/2262541',
            'listed'   => '2026-10-08',
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
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：往訪・対面の予定があれば出社',
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
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：「原則」フルリモート、往訪・対面の予定があれば出社',
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
            'contract' => 'インターン',
            'url'      => 'https://www.wantedly.com/projects/104947',
            'listed'   => '2026-10-08',
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
            'contract' => 'インターン（記事ごとの成果報酬）',
            'url'      => 'https://www.in-fra.jp/long-internships/11734',
            'listed'   => '2026-10-08',
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
            'contract' => 'インターン',
            'url'      => 'https://www.wantedly.com/projects/2525043',
            'listed'   => '2026-10-08',
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
            'contract' => 'インターン',
            'url'      => 'https://www.wantedly.com/projects/2056107',
            'listed'   => '2026-10-08',
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
            'contract' => 'パート・契約・業務委託',
            'url'      => 'https://www.wantedly.com/projects/624928',
            'listed'   => '2026-10-08',
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
            'contract' => 'インターン',
            'url'      => 'https://www.wantedly.com/projects/2542638',
            'listed'   => '2026-10-08',
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
            'contract' => 'インターン',
            'url'      => 'https://www.wantedly.com/projects/1977408',
            'listed'   => '2026-10-08',
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
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：福岡での合宿が定期的にある',
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
            'contract' => 'インターン（雇用契約）',
            'url'      => 'https://01intern.com/job/7813.html',
            'listed'   => '2026-10-08',
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
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：「基本は」フルリモート、年2回の全社会議がオフライン',
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
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：「基本は」フルリモート',
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
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：1都3県在住者は研修期間の出社が推奨',
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
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：「一部リモート」の記載と通勤手当がある',
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
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：撮影・イベント時は現地参加',
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
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/v1/caster/2W3I-8SlQuLn',
            'listed'   => '2026-10-08',
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
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/v1/caster/BF7o6cbuRriP',
            'listed'   => '2026-10-08',
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
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/v1/knithy202303/JaA0imqhrpFw',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-29',
            'closed'   => true, // 2026-10-29 募集ページが非公開になっていた
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
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/v1/knithy202303/NmVmLfWEJpZf',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-29',
            'closed'   => true, // 2026-10-29 募集ページが非公開になっていた
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
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2545514',
            'listed'   => '2026-10-08',
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
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2166709',
            'listed'   => '2026-10-08',
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
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2564978',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-29',
            'closed'   => true, // 2026-10-29 募集ページに「募集終了しました」と表示
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
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2169549',
            'listed'   => '2026-10-08',
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
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2589775',
            'listed'   => '2026-10-08',
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
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2399992',
            'listed'   => '2026-10-08',
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
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2450817',
            'listed'   => '2026-10-08',
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
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2527967',
            'listed'   => '2026-10-08',
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
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2263544',
            'listed'   => '2026-10-08',
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
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：最初の1か月は出社推奨',
            'caution'  => '最初の1か月は出社が推奨されています。',
        ),
        array(
            'slug'     => 'jmty-cs-supporter',
            'company'  => '株式会社ジモティー',
            'title'    => '地元掲示板「ジモティー」のカスタマーサポート',
            'category' => 'カスタマーサポート',
            'summary'  => '投稿内容がルールに合っているかの確認から始め、慣れたらメール・チャットでの問い合わせに対応します（電話対応なし）。',
            'remote'   => '完全在宅・フルリモート（研修中もリモート）',
            'pay'      => '時給1,300円（土日祝1,400円）',
            'hours'    => '週5日・9〜18時の固定シフト（土日祝の勤務あり）',
            'target'   => '経験不問（PCの基本操作と正しい敬語）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/jmty/jobs/-VgoexHpXtjD',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'trifa-cs',
            'company'  => '株式会社トリファ',
            'title'    => '海外eSIMアプリのカスタマーサポート',
            'category' => 'カスタマーサポート',
            'summary'  => '海外旅行用eSIMアプリの問い合わせに、チャットとメールを中心に対応します。FAQの改善も担当します。',
            'remote'   => '基本フルリモート',
            'pay'      => '時給1,300〜1,500円',
            'hours'    => '週5日・1日8時間の3交代制（早朝・日中・夜間）',
            'target'   => 'カスタマーサポートの経験がある方',
            'url'      => 'https://herp.careers/careers/companies/trifa/jobs/ewXZq4RbjaTJ',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：「基本」フルリモートで研修の場所が不明',
        ),
        array(
            'slug'     => 'cosoji-accounting',
            'company'  => 'COSOJI株式会社',
            'title'    => '経理サポート',
            'category' => '事務・経理',
            'summary'  => '見積書・請求書の整理や発行の補助、データ入力、月末月初の入金消し込みなど、経理のアシスタント業務を担当します。',
            'remote'   => '全国フルリモート可',
            'pay'      => '時給1,300〜1,500円',
            'hours'    => '週3〜4日（扶養内も可）・平日9〜18時の間',
            'target'   => '経理経験3年以上（アルバイト・派遣での経験も可）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/cosoji/jobs/IMNt43jKHj2T',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-19',
            'closed'   => true, // 2026-10-19 募集ページが削除されていた
        ),
        array(
            'slug'     => 'vividgarden-marketing-support',
            'company'  => '株式会社ビビッドガーデン',
            'title'    => '産直EC「食べチョク」の営業企画・マーケティングサポート',
            'category' => 'マーケティング',
            'summary'  => '購買データの集計・分析や、生産者向けの提案資料・レポートの作成など、営業企画のサポートをします。',
            'remote'   => '完全在宅可・全国どこからでも',
            'pay'      => '時給1,400円〜',
            'hours'    => '週5日・9〜18時のうち6〜8時間',
            'target'   => '経験不問（データや資料づくりに抵抗がない方）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/vividgarden/jobs/91z3tTiwnugO',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'vividgarden-producer-support',
            'company'  => '株式会社ビビッドガーデン',
            'title'    => '生産者サポート・バイヤーアシスタント',
            'category' => '事務・アシスタント',
            'summary'  => '農家や漁師などの生産者と社内の間に立ち、やりとりや案件の調整を補佐します。',
            'remote'   => 'フルリモート勤務可能',
            'pay'      => '時給1,400〜1,600円',
            'hours'    => '週5日・9〜18時のうち6〜8時間',
            'target'   => '経験不問（メールや電話でのやりとりに抵抗がない方）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/vividgarden/jobs/q23LqDQ4kZ_I',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-29',
            'closed'   => true, // 2026-10-29 募集ページが非公開になっていた
        ),
        array(
            'slug'     => 'lr-furusato-support',
            'company'  => 'LR株式会社',
            'title'    => 'ふるさと納税サイトの運営サポート',
            'category' => '事務・EC運営',
            'summary'  => 'ふるさと納税サイトの返礼品ページの登録・変更・チェック、画像の挿入、在庫の調整などを担当します。',
            'remote'   => 'フルリモートワーク',
            'pay'      => '時給1,100円〜',
            'hours'    => '平日9〜16時（応相談）・土日祝休み',
            'target'   => 'ECサイトでの商品登録やスプレッドシートの経験がある方',
            'contract' => 'アルバイト（パート）',
            'url'      => 'https://herp.careers/careers/companies/lrinc/jobs/DSGgmZbjb16D',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'ichiro-customer-advisor',
            'company'  => 'イチロウ株式会社',
            'title'    => '介護サービスのカスタマーアドバイザー',
            'category' => 'カスタマーサポート',
            'summary'  => '保険外の介護サービスに問い合わせたご家族に、電話とメールでサービスを案内し、契約までをサポートします（テレアポではありません）。',
            'remote'   => '原則フルリモート',
            'pay'      => '時給2,000円',
            'hours'    => '週4〜5日（土日祝を含む）・1日6時間以上',
            'target'   => '接客・営業などの経験3年以上の方',
            'url'      => 'https://herp.careers/careers/companies/link/jobs/OBzDlUrfZ169',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：「原則」フルリモート',
        ),
        array(
            'slug'     => 'zeals-ad-designer',
            'company'  => '株式会社ZEALS',
            'title'    => '広告クリエイティブのデザイナー',
            'category' => 'デザイン',
            'summary'  => 'Google・Meta・LINEなどの広告に使うバナーや動画、チャットボット用の画像を制作します。',
            'remote'   => 'フルリモート（在宅勤務）可能',
            'pay'      => '時給2,000円',
            'hours'    => '週3〜4日以上・10〜19時（相談可）',
            'target'   => 'デザイン経験3年以上（ポートフォリオ必須）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/zeals/jobs/1qibks78X2Q0',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-22',
            'closed'   => true, // 2026-10-22 募集ページが削除されていた
        ),
        array(
            'slug'     => 'hackazouk-web-designer',
            'company'  => '株式会社ハッカズーク',
            'title'    => '生成AIを使うWeb・グラフィックデザイナー',
            'category' => 'デザイン',
            'summary'  => '企業の人事担当者向けのバナーやLPを、生成AIも使いながら制作します。',
            'remote'   => 'フルリモート可',
            'pay'      => '時給2,500〜3,500円',
            'hours'    => '月40〜50時間',
            'target'   => 'BtoBのWebデザイン実務1年以上',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/hackazouk30/jobs/ssnJpNZDy3co',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-19',
            'closed'   => true, // 2026-10-19 募集ページが削除されていた
        ),
        array(
            'slug'     => 'luche-web-designer',
            'company'  => 'LUCHE GROUP',
            'title'    => 'ギフトECサイトのWebデザイナー',
            'category' => 'デザイン',
            'summary'  => 'ギフト専門ECサイトのバナー・LP・商品ページや、パッケージ、名入れのデザインを担当します。',
            'remote'   => 'フルリモート（自宅で勤務）',
            'pay'      => '時給2,000円',
            'hours'    => '平日週5日・1日5時間〜',
            'target'   => 'ECのWebデザイン実務3年以上・イラスト制作の経験',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/luchegroup/jobs/AekDhrH_aV7y',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'synesthesias-unity',
            'company'  => '株式会社シナスタジア',
            'title'    => 'Unityエンジニア（3D都市モデル）',
            'category' => 'エンジニア',
            'summary'  => '東大発のITベンチャーで、3D都市モデルを使ったまちづくりツールなどの開発を担当します。',
            'remote'   => 'フルリモートワーク',
            'pay'      => '時給3,000〜4,000円',
            'hours'    => '月80〜160時間（フルフレックス）',
            'target'   => 'Unityでの開発経験がある方',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/synesthesias/jobs/ntpBeWqYtj5Z',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'terass-legal-assistant',
            'company'  => '株式会社TERASS',
            'title'    => '不動産のリーガルアシスタント',
            'category' => '事務・リーガル',
            'summary'  => '不動産売買の重要事項説明書や売買契約書の作成とチェックを担当します。',
            'remote'   => '完全フルリモート',
            'pay'      => '時給3,000円程度（派遣の場合）',
            'hours'    => '週4〜5日・9〜18時のうち8時間（時短も相談可）',
            'target'   => '不動産の契約書・重説の作成経験がある方',
            'contract' => '業務委託・契約社員・派遣社員',
            'url'      => 'https://herp.careers/careers/companies/terass/jobs/JvcgM46r-NDN',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'clas-sales-admin',
            'company'  => '株式会社クラス',
            'title'    => '営業事務',
            'category' => '事務・アシスタント',
            'summary'  => '契約書・見積書などの書類の作成と回収、メールでのお客様対応、納品までのスケジュール管理を担当します。',
            'remote'   => 'フルリモート勤務可能',
            'pay'      => '時給1,500円〜',
            'hours'    => '週5日・1日8時間（土日祝休み）',
            'target'   => '営業事務などの事務経験がある方',
            'url'      => 'https://herp.careers/careers/companies/clas/jobs/t4g7wcczs5k2',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：毎週水曜の出社推奨',
            'caution'  => '毎週水曜日の出社が推奨されています（必須ではありません）。',
        ),
        array(
            'slug'     => 'linkties-ai-assistant',
            'company'  => 'リンクタイズホールディングス株式会社',
            'title'    => '生成AIを使う事務アシスタント',
            'category' => '事務・アシスタント',
            'summary'  => '社内資料のテキスト化やデータの整形、生成AIの回答の確認など、AIを使った事務作業を担当します。',
            'remote'   => 'フルリモートワーク',
            'pay'      => '時給1,300円〜',
            'hours'    => '週3日〜・1日4時間以上（平日10〜19時）',
            'target'   => '社会人経験のある方（職種は問わない）',
            'url'      => 'https://herp.careers/careers/companies/linktieshd/jobs/bwg-VrcDbyg0',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：入社後約1か月の出社と年数回の出社',
            'caution'  => '入社後の約1か月は出社があり、その後も年数回ほど出社をお願いされる場合があります。',
        ),
        array(
            'slug'     => 'cosoji-ai-engineer',
            'company'  => 'COSOJI株式会社',
            'title'    => 'AIエンジニア（業務委託）',
            'category' => 'エンジニア',
            'summary'  => '不動産管理のデータを使ったAIソリューションの開発と、社内業務の効率化を担当します。',
            'remote'   => 'フルリモート・完全在宅可',
            'pay'      => '時給3,500〜6,000円',
            'hours'    => '週4日以上・週32時間以上（平日日中）',
            'target'   => 'Python開発3年以上・LLMを使った開発経験',
            'url'      => 'https://herp.careers/careers/companies/cosoji/jobs/mF_DM2_4AiaI',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：東京近郊在住者は客先訪問あり',
            'caution'  => '東京近郊に住んでいる方は、お客様先への訪問をお願いされる場合があります。',
        ),
        array(
            'slug'     => 'mediafirst-writer',
            'company'  => '株式会社メディアファースト',
            'title'    => '広告・メルマガの文章をつくるライター',
            'category' => 'ライティング',
            'summary'  => 'Web広告の会社で、募集文やLP・メルマガの原稿づくりと、公開後の数字を見た改善を担当します。最初は決まった型に沿った文章から始めます。',
            'remote'   => '完全在宅・フルフレックス（出社なし）',
            'pay'      => '成果に応じて決定',
            'hours'    => '週15時間以上（土日・早朝・深夜も可）',
            'target'   => '未経験歓迎・副業可',
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2305752',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'carestyle-health-writer',
            'company'  => '株式会社ケア・スタイル',
            'title'    => '健康・美容記事の在宅ライター',
            'category' => 'ライティング',
            'summary'  => '自社サイトに載せる健康・美容などの記事を執筆します。納期は自分で決められます。',
            'remote'   => '在宅ライター（執筆は自宅で完結）',
            'pay'      => '1文字0.5円〜（能力に応じて0.7〜1.3円程度まで）',
            'hours'    => '納期・稼働は自分で決められる',
            'target'   => '実務経験や専門分野がある方は優遇',
            'url'      => 'https://www.carestyle.co.jp/recruit/writer',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'centerglobe-seo-writer',
            'company'  => '株式会社センターグローブ',
            'title'    => 'SEO記事の在宅ライター',
            'category' => 'ライティング',
            'summary'  => '記事作成代行の会社から依頼を受け、さまざまなジャンルのSEO記事を執筆します。',
            'remote'   => '在宅ライター',
            'pay'      => '1文字1.6円〜（90日後から1.6〜2.4円）',
            'hours'    => '執筆量は月ごとに自分で決められる',
            'target'   => 'SEO記事の執筆経験1年以上（テスト記事あり）',
            'url'      => 'https://xn--3kq3hlnz13dlw7bzic.jp/shuzaidaikou/recruitment-writer/',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'magia-manga-editor',
            'company'  => '株式会社MAGIA',
            'title'    => 'マンガ編集者',
            'category' => '編集',
            'summary'  => '自社のマンガ作品の企画を立ち上げ、作家さんの発掘から制作の進行、監修までを担当します。',
            'remote'   => '完全在宅OK・勤務時間自由',
            'pay'      => '',
            'hours'    => '勤務時間は自由',
            'target'   => 'マンガ編集の経験がある方',
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/1454887',
            'listed'   => '2026-10-08',
            'checked'  => '2026-10-08',
        ),
        array(
            'slug'     => 'kkgen-ai-engineer',
            'company'  => '株式会社KK Generation',
            'title'    => '建設図面AIの研究開発エンジニア',
            'category' => 'エンジニア（AI）',
            'summary'  => '建設図面に特化したAIの研究開発、大手ゼネコンの実図面を使った検証、顧客向けのWebアプリ開発のうち、希望の領域を担当します。最初は週5〜10時間ほどの簡単な案件から始めます。',
            'remote'   => 'フルリモート・シフト自由',
            'pay'      => '時給2,000〜6,000円（経験者は時給3,500円〜）',
            'hours'    => '週2日以上・週10時間以上',
            'target'   => 'Pythonで機械学習を実装した経験がある方（授業・研究室での経験も可）',
            'contract' => '業務委託',
            'url'      => 'https://ut-board.com/recruitment/541/',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'livco-sns-marketing',
            'company'  => '株式会社LivCo',
            'title'    => '東南アジア向けSNSマーケティング',
            'category' => 'SNSマーケティング',
            'summary'  => '東南アジアの若者に、日本での仕事や生活の情報をFacebook・TikTok・Instagramで届けます。企画から配信、数字をもとにした改善まで担当します。翻訳は各国出身のスタッフが行うので、外国語は不要です。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,230〜1,500円',
            'hours'    => '週3日以上・週24時間以上',
            'target'   => '未経験OK（SNS運用に興味がある方）',
            'url'      => 'https://ut-board.com/recruitment/721/',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：社内合宿あり（必須か不明）',
            'caution'  => '社内合宿があると書かれています。参加が必須かどうかは応募前に確認してください。',
        ),
        array(
            'slug'     => 'proseltraction-web-ops',
            'company'  => '株式会社プロセルトラクション',
            'title'    => 'WordPress・HubSpotのWeb運用エンジニア',
            'category' => 'Web運用エンジニア',
            'summary'  => 'WordPressのサイト管理やSEO対策、HubSpotとSlackの連携設定など、マーケティングと営業を支える仕組みづくりを担当します。広告運用やデータ入力の補助もあります。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,500〜3,500円（能力に応じて決定）',
            'hours'    => '週2日以上・週3時間以上',
            'target'   => 'HTML/CSSかプログラミングの経験があり、WordPressを使ったことがある方（学歴不問・高専生や専門学生も可）',
            'contract' => 'インターン契約',
            'url'      => 'https://ut-board.com/recruitment/688/',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'storerecord-fullstack',
            'company'  => 'ストアレコード株式会社',
            'title'    => '小売向け経営管理SaaSのフルスタック開発',
            'category' => 'エンジニア',
            'summary'  => '小売企業の経営データを一か所で管理するSaaSの開発全般を担当します。予実管理や経営分析の自動化、外部アプリとの連携機能などを作っていきます。',
            'remote'   => 'フルリモート・フルフレックス（土日祝の勤務も可）',
            'pay'      => '時給2,000〜5,000円（能力に応じて決定）',
            'hours'    => '週3日以上・週15時間以上',
            'target'   => 'Webアプリの要件定義から運用・保守までを経験したことがある方',
            'contract' => '業務委託',
            'url'      => 'https://ut-board.com/recruitment/406/',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'nexuslink-career-app',
            'company'  => 'NexusLink株式会社',
            'title'    => '就活アプリの運営・SNSマーケティング',
            'category' => 'SNS・マーケティング',
            'summary'  => '就活生向けアプリの運営、SNSでの発信、利用者との面談サポート、営業のアシスタントを担当します。施策を考えるところから実行まで任されます。',
            'remote'   => '完全リモート（面接2回もオンライン）',
            'pay'      => '完全成果報酬（1件2,000〜4,000円）',
            'hours'    => '週3日以上・週15時間以上・フルフレックス',
            'target'   => '学年不問・インターン未経験OK・就活中の学生も可',
            'contract' => '業務委託（完全成果報酬）',
            'url'      => 'https://ut-board.com/recruitment/609/',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'caution'  => '業務委託・完全成果報酬のため、時給の保証はありません（募集ページの時給1,500〜2,000円は過去の実績からの目安です）。',
        ),
        array(
            'slug'     => 'unseed-ai-engineer',
            'company'  => '株式会社Unseed',
            'title'    => '企業向けAI開発エンジニア（東大松尾研発）',
            'category' => 'エンジニア（AI）',
            'summary'  => '企業ごとの課題に合わせたAI開発の実案件に参加します。機械学習や生成AIのモデル開発から、Webシステムへの組み込みまでを担当します。',
            'remote'   => 'フルリモート（業務は基本的にリモート）',
            'pay'      => '時給1,700〜4,000円（能力に応じて決定）',
            'hours'    => '週12時間以上（平日10〜17時はクライアント対応あり）',
            'target'   => 'LLM・画像認識などのAIモデルを設計・実装した経験がある方',
            'url'      => 'https://ut-board.com/recruitment/786/',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：「業務は基本的にリモート」',
        ),
        array(
            'slug'     => 'herp-engineer-intern',
            'company'  => '株式会社HERP',
            'title'    => '採用管理SaaSのエンジニア',
            'category' => 'エンジニア',
            'summary'  => '採用管理のSaaSを作る会社で、既存・新規事業の機能開発や運用改善、技術的な課題の解決に携わります。興味に応じて担当を決めます。',
            'remote'   => 'フルリモート（在宅勤務可）・フルフレックス',
            'pay'      => '時給1,500円〜',
            'hours'    => '平日に週15時間以上・1か月以上',
            'target'   => 'プログラミングの実務経験がある方（学歴・学部不問、高専生・専門学生も可）',
            'contract' => 'インターン契約',
            'url'      => 'https://ut-board.com/recruitment/357/',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'yappli-data-intern',
            'company'  => '株式会社ヤプリ',
            'title'    => 'アプリ行動データのデータサイエンティスト',
            'category' => 'データ分析エンジニア',
            'summary'  => 'アプリの利用データを使ったプロダクトの企画・開発や、社内向けBIツール・分析用データの整備に関わります。テーマは面談で希望を聞いて決めます。',
            'remote'   => 'フルリモート（インターンはフルリモート可）',
            'pay'      => '時給1,800〜2,500円',
            'hours'    => '週3日以上・平日日中に週24時間以上（1か月程度〜）',
            'target'   => '学生（学歴不問）で、機械学習や統計モデルの開発経験がある方（新卒の就職先が決まっている方は対象外）',
            'contract' => 'インターン契約',
            'url'      => 'https://ut-board.com/recruitment/514/',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'salesnow-fullstack',
            'company'  => '株式会社SalesNow',
            'title'    => 'AIネイティブ・フルスタックエンジニア',
            'category' => 'エンジニア',
            'summary'  => 'Next.js・TypeScript・Pythonを使ったWebアプリの開発や、AIエージェントなど新しい機能の開発を担当します。社員と同じコードで、設計から本番リリースまで担います。',
            'remote'   => 'リモートワーク可・出社義務なし',
            'pay'      => '時給1,500円〜（経験・能力に応じて決定）',
            'hours'    => '週4日以上・月160時間程度',
            'target'   => '大学・大学院生（学部1年から可）で、自分で設計を判断した開発経験があり、1年以上働ける方',
            'contract' => 'アルバイト',
            'url'      => 'https://renew-career.com/companies/512/joboffers/1556',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-22',
            'closed'   => true, // 2026-10-22 募集ページに「募集を終了しました」と表示
        ),
        array(
            'slug'     => 'timers-school-sales',
            'company'  => '株式会社Timers',
            'title'    => 'オンラインスクールの電話説明会スタッフ',
            'category' => '電話営業',
            'summary'  => 'オンラインスクールの資料を請求した方に、電話で約1時間の説明会を行い、講座を提案します。申込までのフォローとCRMへの入力も担当します。',
            'remote'   => '在宅勤務OK（恵比寿のオフィス勤務も選べる）',
            'pay'      => '時給1,500円＋申込1件ごとのインセンティブ',
            'hours'    => '週4日・週16時間以上（1日4時間以上、9〜21時の間）',
            'target'   => '大学生・大学院生（学年不問・未経験OK）',
            'contract' => '業務委託',
            'url'      => 'https://renew-career.com/companies/431/joboffers/1174',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'caution'  => '業務委託です。海外在住・留学中の方は応募できません。',
        ),
        array(
            'slug'     => 'bluefield-research-engineer',
            'company'  => 'Bluefield Energy株式会社',
            'title'    => '電力・再エネのリサーチエンジニア',
            'category' => 'リサーチエンジニア',
            'summary'  => '太陽光発電量や電力市場価格の予測、蓄電池の運用を最適化するアルゴリズムの研究と実装を担当します。論文を実装に落とし込む仕事もあります。',
            'remote'   => 'フルリモートOK（選考もオンライン）',
            'pay'      => '時給1,500円〜',
            'hours'    => '週2日以上・週10時間以上（9〜19時の間）',
            'target'   => '大学生・大学院生で、機械学習または数理最適化をPythonで実装した経験がある方',
            'url'      => 'https://renew-career.com/companies/462/joboffers/1639',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：勤務地欄にオフィスの記載があり出社の有無が不明',
            'caution'  => '勤務地欄に新橋のオフィスも書かれています。出社がまったくないかは応募前に確認してください。',
        ),
        array(
            'slug'     => 'slogan-internstreet',
            'company'  => 'スローガン株式会社',
            'title'    => '長期インターン紹介サービスの運営インターン',
            'category' => '営業・マーケ',
            'summary'  => '長期インターン専門サービス「Intern Street」で、学生との面談、企業への営業、事業企画、マーケティングなどを社員と同じ裁量で担当します。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,226円（昇給あり）',
            'hours'    => '面談で相談',
            'target'   => '大学生（学年不問）',
            'url'      => 'https://internstreet.jp/jobs/slogan-1402',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'hokuto-medical-proofreader',
            'company'  => '株式会社HOKUTO',
            'title'    => '医療系資材の校正・校閲',
            'category' => '校正・編集',
            'summary'  => '製薬会社が医師向けに出す資料や、医療系の記事の校正・校閲を担当します。グラフや図表が元の資料と合っているかの確認もあります。',
            'remote'   => 'フルリモート・在宅勤務・フルフレックス',
            'pay'      => '時給2,000円',
            'hours'    => '週3日以上・1日4時間以上',
            'target'   => '医療系の広告代理店・出版社・製薬会社などで、医療資材の校正・校閲を1年以上経験した方',
            'contract' => 'アルバイト・業務委託・契約社員',
            'url'      => 'https://herp.careers/careers/companies/hokuto/jobs/F86TFKGMnCFG',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'upsider-inside-sales',
            'company'  => '株式会社UPSIDER',
            'title'    => '法人カードのインサイドセールス',
            'category' => 'インサイドセールス',
            'summary'  => '法人カードなどに興味を持った企業へ電話でヒアリングし、申込から利用開始後のフォローまで担当します。結果のデータをもとにした改善提案も行います。',
            'remote'   => '日本国内からフルリモート（渋谷オフィスへの出社は希望者のみ）',
            'pay'      => '時給1,300円〜',
            'hours'    => '平日週4〜5日・週30時間以上（10〜19時）・1年以上',
            'target'   => '学生・インターン未経験OK（PCの基本操作ができる方）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/upsider/jobs/MTGIwO6UfjWx',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'doctormate-oncall-nurse',
            'company'  => 'ドクターメイト株式会社',
            'title'    => '介護施設の夜間オンコール看護師',
            'category' => 'オンライン看護（電話サポート）',
            'summary'  => '介護施設から夜間にかかってくる相談の電話に自宅から対応し、緊急度を判断して指示を出します。マニュアルを見ながら記録をPCで作成します。',
            'remote'   => '自宅でのフルリモート（選考もすべてオンライン）',
            'pay'      => '時給1,600〜2,700円（深夜手当などを含む）',
            'hours'    => '週2〜3日以上・夜間のシフト制（17〜22時、22〜翌3時など。最初の1〜2か月は19〜22時）',
            'target'   => '正看護師の資格があり、看護師の実務経験が5年以上ある方',
            'url'      => 'https://herp.careers/careers/companies/doctormate/jobs/TdPdR3QHbRrm',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：研修が対面かオンラインか不明',
            'caution'  => '待機を始める前の研修（平日10〜15時）が対面かオンラインかは書かれていません。応募前に確認してください。',
        ),
        array(
            'slug'     => 'directsourcing-recruit-assistant',
            'company'  => '株式会社ダイレクトソーシング',
            'title'    => '採用アシスタント',
            'category' => '採用アシスタント',
            'summary'  => '面接の日程調整や関係者への連絡、選考状況や評価データの管理、採用媒体の運用などを担当します。',
            'remote'   => '原則フルリモート（日本国内から）',
            'pay'      => '時給1,300円〜',
            'hours'    => '平日週5日・1日5時間（10〜15時または11〜16時）',
            'target'   => '社会人経験5年以上・人事か採用事務の経験3年以上で、リモートワークの経験がある方',
            'url'      => 'https://herp.careers/careers/companies/directsource/jobs/ncnXGoVUfKZw',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：「原則」フルリモート',
        ),
        array(
            'slug'     => 'directsourcing-sourcer',
            'company'  => '株式会社ダイレクトソーシング',
            'title'    => 'スカウト担当（ソーサー）',
            'category' => '採用（スカウト）',
            'summary'  => 'LinkedInやビズリーチなどから採用したい人材を探し、スカウトメールを送ります。',
            'remote'   => '原則フルリモート（日本国内から）',
            'pay'      => '時給2,500〜4,500円',
            'hours'    => '週10〜20時間程度（平日のシフト制）',
            'target'   => 'ダイレクトリクルーティング（スカウト）の経験と、リモートワークの経験がある方',
            'url'      => 'https://herp.careers/careers/companies/directsource/jobs/s9i53NvFrplV',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'excluded' => '2026-10-13 掲載基準の見直しで除外：「原則」フルリモート',
        ),
        array(
            'slug'     => 'risuma-teleapo',
            'company'  => '株式会社リスマ',
            'title'    => 'ネットショップ運営企業へのテレアポ',
            'category' => 'テレアポ・営業',
            'summary'  => 'ネットショップを運営する企業に電話をかけ、関心を持った企業を社員との打ち合わせにつなぎます。見積もりや契約は社員が担当します。',
            'remote'   => '完全在宅（面接・研修もオンラインで、初回の出社なし）',
            'pay'      => '時給1,600〜2,000円＋アポ1件2,000円',
            'hours'    => '週3〜4日・1日4〜6時間（平日10〜17時の間）',
            'target'   => '法人向けテレアポか、電話でアポイントを取った経験がある方',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/resmahr/jobs/ERy5s4lZ6AjC',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'startupkenpo-cs',
            'company'  => 'VCスタートアップ健康保険組合（一般社団法人VCスタートアップ労働衛生推進協会）',
            'title'    => '健康保険組合のカスタマーサポート・保険事務',
            'category' => 'カスタマーサポート',
            'summary'  => '加入者や加入企業からの問い合わせに、メールとチャットで対応します。保険事務のサポートやFAQの作成・更新も担当します。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,500〜2,500円',
            'hours'    => '平日8〜18時の間・シフト制（1日8時間以内）',
            'target'   => '経験不問（PC作業に抵抗がなく、文章でのやりとりができる方）',
            'contract' => 'アルバイト・業務委託',
            'url'      => 'https://herp.careers/careers/companies/startupkenpo/jobs/WZ_exPllR49g',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'leaner-ops-staff',
            'company'  => '株式会社Leaner Technologies',
            'title'    => '調達SaaSの事務オペレーションスタッフ',
            'category' => '事務・オペレーション',
            'summary'  => '調達・購買向けSaaSの裏方として、システムのデータの編集・更新、ユーザーアカウントの管理、データの突き合わせとチェックを担当します。社内の連絡はSlackです。',
            'remote'   => '完全在宅（フルリモート）',
            'pay'      => '時給1,400円〜',
            'hours'    => '平日週5日・9〜18時（9〜17時の時短も相談可）',
            'target'   => '企業での事務経験がある方（営業事務・一般事務など）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/leaner/jobs/InSZ4_rsO6nX',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'marsdy-excel-office',
            'company'  => '株式会社Marsdy',
            'title'    => '在宅の事務スタッフ（Excel中心）',
            'category' => '事務',
            'summary'  => '法人向けDXサービスを運営する会社で、Excelを中心としたデータ処理、業務ツールの操作、データの入力・更新、レポート作成を担当します。',
            'remote'   => 'フルリモート（出社の必要なし・面接もオンライン）',
            'pay'      => '時給1,200円〜',
            'hours'    => '週5日・8時30分〜18時の間',
            'target'   => 'Excel関数で業務を効率化した経験か、RPAで業務を自動化した経験がある方（Windows PCが必要）',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/marsdy/jobs/DX-mwi2EMhT4',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'caution'  => 'このページからは応募できないため、募集ページにある企業サイトから応募してください。',
        ),
        array(
            'slug'     => 'kumono-media-intern',
            'company'  => '株式会社Kumono',
            'title'    => '新しいメディアの立ち上げ（学生インターン）',
            'category' => 'ライティング・メディア',
            'summary'  => '代表と一緒に、ニッチな分野に特化したメディアを立ち上げます。記事やSNSの制作、市場・競合の調査、AIを使った仕組みづくりなど、その時々に必要なことを担当します。',
            'remote'   => 'フルリモート可（対面は任意）',
            'pay'      => '時給1,230円〜',
            'hours'    => '週3日・1日3時間から・3か月以上',
            'target'   => '学生（学年・専攻・経験は不問。文章を書いたり読んだりするのが得意な方）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/kumono/jobs/5oRvYdn9uwJK',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'lpixel-annotator',
            'company'  => 'エルピクセル株式会社',
            'title'    => '創薬AIのための画像アノテーター',
            'category' => 'データ作成・アノテーション',
            'summary'  => '製薬研究を支えるAIの開発のために、生物の画像に印を付けるアノテーションを担当します。モデルの作成と評価まで関わります。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,300円〜',
            'hours'    => '週1〜5日・1回2〜7.5時間（5〜22時の好きな時間）',
            'target'   => '顕微鏡画像を扱った経験がある方',
            'contract' => 'アルバイト（非常勤）',
            'url'      => 'https://herp.careers/careers/companies/lpixel/jobs/pFHdZktYcDnE',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'lpixel-ml-engineer',
            'company'  => 'エルピクセル株式会社',
            'title'    => '医療AIの機械学習エンジニア',
            'category' => 'エンジニア（AI）',
            'summary'  => '医師や研究者を支えるAIの開発で、データの分析から機械学習モデルの作成、最新のアルゴリズムの実装と評価までを担当します。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,800円〜',
            'hours'    => '週1〜5日・1回2〜7.5時間（5〜22時の好きな時間）',
            'target'   => 'Pythonで画像処理・機械学習をした経験がある方',
            'contract' => 'アルバイト（非常勤）',
            'url'      => 'https://herp.careers/careers/companies/lpixel/jobs/BefYJL2grETt',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'procloud-client-support',
            'company'  => '株式会社プロクラ',
            'title'    => '住宅会社向けSaaSのクライアントサポート',
            'category' => 'カスタマーサポート',
            'summary'  => '契約している住宅会社に電話やWeb会議で連絡し、自社のITツールがどう使われているかを確認します。聞いた内容は社内の営業チームに共有します。',
            'remote'   => '全国どこからでも完全在宅（通勤なし）',
            'pay'      => '時給1,350〜1,800円',
            'hours'    => '週4日以上・週24時間以上（中抜けも可）',
            'target'   => '社会人経験がある方（業界の知識は入社後の研修で）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/procloud/jobs/L4tmWpNeB6Ei',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'leadfactory-btob-marketing',
            'company'  => '株式会社リードファクトリー',
            'title'    => 'BtoBマーケティングのアシスタント（未経験から）',
            'category' => 'マーケティング',
            'summary'  => '先輩のサポートとして、広告運用やコンテンツ制作を経験しながらマーケティングの基礎を身につけます。慣れてきたら、クライアント対応も任されます。',
            'remote'   => 'フルリモート（研修は動画とマニュアルが中心）',
            'pay'      => '時給1,300〜2,000円',
            'hours'    => '平日9〜18時（実働8時間）',
            'target'   => '未経験OK（BtoBマーケティングに興味があり、仕事に使えるPCがある方）',
            'contract' => '正社員・契約社員・アルバイト',
            'url'      => 'https://herp.careers/careers/companies/leadfac/jobs/xDDUEHNnf9jb',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => '3sunny-cs',
            'company'  => '株式会社3Sunny',
            'title'    => '医療・介護向けSaaSのカスタマーサポート',
            'category' => 'カスタマーサポート',
            'summary'  => '医療機関や介護施設向けのサービスで、導入の案内をメールで送ったり、電話で操作をサポートしたりします。慣れてきたら、マニュアルの改善や新しいメンバーへの教育も担当します。',
            'remote'   => 'フルリモートOK',
            'pay'      => '時給1,300円〜',
            'hours'    => '平日週4〜5日・9〜18時のうち5〜8時間',
            'target'   => '未経験OK（メールやチャットでのやりとりと、電話での顧客対応に抵抗がない方）',
            'contract' => '業務委託・契約社員',
            'url'      => 'https://herp.careers/careers/companies/weare3sunny/jobs/zyeZsrgw7bt8',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-29',
            'closed'   => true, // 2026-10-29 募集ページが非公開になっていた
            'caution'  => 'このページからは応募できないため、募集ページにある企業サイトから応募してください。',
        ),
        array(
            'slug'     => 'helpfeel-event-assistant',
            'company'  => '株式会社Helpfeel',
            'title'    => 'マーケティング部のイベント事務アシスタント',
            'category' => '事務アシスタント',
            'summary'  => '展示会やセミナーを担当するマーケターの補佐として、申込の対応、ノベルティや物品の手配、請求書の処理、問い合わせ対応、イベント後の顧客情報の管理を担当します。',
            'remote'   => 'フルリモート',
            'pay'      => '時給2,500〜3,000円',
            'hours'    => '平日週5日・1日8時間程度（週の稼働時間は相談可）',
            'target'   => '展示会やセミナーなど、イベントまわりの事務経験がある方',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/notainc/jobs/LQczqX-R9ASo',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'fronteo-doc-reviewer',
            'company'  => '株式会社FRONTEO',
            'title'    => '不正調査データの確認・仕分け（ドキュメントレビュー）',
            'category' => '事務・データ確認',
            'summary'  => '専用のシステムで、不正調査のプロジェクトのメールやファイルを確認し、ルールに従って仕分けます。マネージャーの指示のもとで進めます。',
            'remote'   => '完全在宅（専用PCを貸与・説明会もTeams）',
            'pay'      => '時給1,800円〜',
            'hours'    => '平日9〜18時（日数・時間は相談可）',
            'target'   => '未経験OK（大卒相当で、WordやExcelの基本操作ができる方）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/fronteo/jobs/h4-cmh985z7i00ftqu07fzg5u4i4',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'caution'  => '機密情報を扱うため、家族を含めて他の人に画面を見られない部屋が必要です。',
        ),
        array(
            'slug'     => 'fronteo-ediscovery-reviewer',
            'company'  => '株式会社FRONTEO',
            'title'    => '電子情報開示のドキュメントレビュー（法律・英語を活かせる）',
            'category' => '事務・データ確認',
            'summary'  => '電子情報開示や不正調査のプロジェクトで、メールやファイルを確認・仕分けします。プロジェクトによっては法律の知識や英語力を活かせます。',
            'remote'   => '完全在宅（専用PCを貸与・説明会もTeams）',
            'pay'      => '時給2,000円〜',
            'hours'    => '平日9〜18時（日数・時間は相談可）',
            'target'   => '大卒相当で、WordやExcelの基本操作ができる方（法律の知識や英語力があれば活かせる）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/fronteo/jobs/h4-cmh9860hp00fuqu07dzbfubnl',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'caution'  => '機密情報を扱うため、家族を含めて他の人に画面を見られない部屋が必要です。',
        ),
        array(
            'slug'     => 'itmedia-nlab-writer',
            'company'  => 'アイティメディア株式会社',
            'title'    => '「ねとらぼ」の編集記者（学生インターン）',
            'category' => 'ライティング・編集',
            'summary'  => 'ネットで話題の情報を集め、記事の企画からオンラインでの取材、執筆までを担当します。編集・校正やSNSでの発信のサポートもあります。',
            'remote'   => '完全在宅OK（出社は希望者のみ）',
            'pay'      => '時給1,200〜1,300円（ほかに手当あり）',
            'hours'    => '週3〜5日・10〜16時（実働5時間）',
            'target'   => '大学2・3年生（学部・学科は不問）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/itmedia/jobs/h4-cmhdll79u02zzs607hm0e3daf',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-29',
            'closed'   => true, // 2026-10-29 募集ページが削除されていた
            'caution'  => 'このページからは応募できないため、募集ページにある企業サイトから応募してください。',
        ),
        array(
            'slug'     => 'dgcircus-qa',
            'company'  => 'デジタルサーカス株式会社',
            'title'    => 'Webサイトの品質管理（QAエンジニア）',
            'category' => 'エンジニア（QA）',
            'summary'  => 'Webサイトのテストの計画から実施、不具合の報告と分析までを担当します。マニュアルや設計書の作成・更新もあります。',
            'remote'   => 'フルリモート（客先常駐なし・オフィスの利用は任意）',
            'pay'      => '時給1,300〜4,000円',
            'hours'    => 'フレックス制（日数は相談）',
            'target'   => '経験・スキルに応じて時給を決定（求める経験は募集ページで確認）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/dgcircus/jobs/h4-cmhdjnz2q01i5s607jh5jyre0',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'caution'  => 'このページからは応募できないため、募集ページにある企業サイトから応募してください。',
        ),
        array(
            'slug'     => 'dgcircus-frontend',
            'company'  => 'デジタルサーカス株式会社',
            'title'    => 'Drupalサイトのフロントエンドエンジニア',
            'category' => 'エンジニア',
            'summary'  => 'CMS「Drupal」のテーマや画面の制作、JavaScriptの開発、UIの設計を担当します。',
            'remote'   => 'フルリモート（客先常駐なし・オフィスの利用は任意）',
            'pay'      => '時給2,000〜5,000円',
            'hours'    => 'フレックス制（日数は相談）',
            'target'   => '経験・スキルに応じて時給を決定（求める経験は募集ページで確認）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/dgcircus/jobs/h4-cmhdjnxx401i4s607dxfu38jw',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
            'caution'  => 'このページからは応募できないため、募集ページにある企業サイトから応募してください。',
        ),
        array(
            'slug'     => 'herp-jobmill-teleapo',
            'company'  => '株式会社HERP',
            'title'    => '人材紹介会社向けシステムのテレアポ',
            'category' => 'テレアポ・営業',
            'summary'  => '人材紹介会社のリストに沿って電話をかけ、自社のシステムを案内して打ち合わせを設定します。人材紹介の知識は不要です。',
            'remote'   => '完全在宅（オフィスの利用は任意）',
            'pay'      => '時給2,500円〜',
            'hours'    => '平日週3日以上・10〜18時（月100〜160時間）・3か月以上',
            'target'   => '電話営業か営業の経験がある方（業界不問）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/herpinc/jobs/qghyUCcvdNXt',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'herp-recruit-evaluator',
            'company'  => '株式会社HERP',
            'title'    => '求職者の推薦リストの評価（IT採用の経験者）',
            'category' => '採用サポート',
            'summary'  => 'AIが推薦した求職者のリストを、求人の条件と照らし合わせて評価・選別します。推薦の精度を上げるためのフィードバックも担当します。',
            'remote'   => 'フルリモート',
            'pay'      => '時給3,000円〜',
            'hours'    => '週5日・1日2〜8時間（慣れたら時間帯の調整・夜間も可）',
            'target'   => 'IT業界の採用で5年以上の実務経験があり、エンジニア採用に詳しい方',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/herpinc/jobs/bdQ6H2BWMNm4',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'lr-banner-designer',
            'company'  => 'LR株式会社',
            'title'    => 'ふるさと納税サイトのバナー・LPデザイナー',
            'category' => 'デザイン',
            'summary'  => 'ふるさと納税サイトのサムネイル、バナー、LP用の画像をPhotoshopで作ります。商品写真の加工や合成もあります（コーディングは基本なし）。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,100円〜',
            'hours'    => '平日9〜16時（相談可）',
            'target'   => 'PhotoshopでECのLPやバナーを作った実務経験（3年が目安）がある方（ポートフォリオが必要）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/lrinc/jobs/KuXcmiW6zvWq',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'jmty-cs-disability',
            'company'  => '株式会社ジモティー',
            'title'    => '【障がい者採用】ジモティーの投稿チェック・カスタマーサポート',
            'category' => 'カスタマーサポート',
            'summary'  => '地元の掲示板「ジモティー」の投稿がルールに合っているかを確認します。慣れてきたら、メールやチャットでの問い合わせにも対応します。',
            'remote'   => '完全在宅（面接・研修もオンライン）',
            'pay'      => '時給1,180〜1,400円（住む地域と曜日による）',
            'hours'    => '週5日・9〜18時の曜日固定シフト',
            'target'   => '障害者手帳をお持ちの方（取得予定も可）で、社会人経験がある方',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/jmty/jobs/udPBHkavP7o4',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-22',
            'closed'   => true, // 2026-10-22 募集ページが削除されていた
            'caution'  => '個室で働けることと、自宅の固定回線（ポケットWi-Fiは不可）が必要です。',
        ),
        array(
            'slug'     => 'prointern-inside-sales',
            'company'  => 'プロ・インターン株式会社',
            'title'    => '企業の商談獲得（インサイドセールス）',
            'category' => 'インサイドセールス',
            'summary'  => '取引先の案件に入り、問い合わせをくれた企業への電話やメールで商談を獲得します。日程の調整や記録の入力も担当します。',
            'remote'   => 'フルリモート（完全在宅）',
            'pay'      => '時給1,500円〜',
            'hours'    => '週3日以上・1日4時間以上（8〜19時の間でシフト自由）',
            'target'   => '営業の経験があると歓迎（必須の条件は記載なし）',
            'contract' => '業務委託・アルバイト',
            'url'      => 'https://herp.careers/careers/companies/prointern/jobs/7JyohZNuiaXL',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'prointern-intern',
            'company'  => 'プロ・インターン株式会社',
            'title'    => '在宅のインサイドセールス（インターン）',
            'category' => 'インサイドセールス',
            'summary'  => '取引先のサービスについて、問い合わせをくれた企業への電話やメール、日程の調整、記録の入力を担当します。',
            'remote'   => 'フルリモート（完全在宅）',
            'pay'      => '時給1,200円〜',
            'hours'    => '週3日以上・1日4時間以上・3か月以上（8〜19時の間）',
            'target'   => '週3日・3か月以上働ける方（経験は不問）',
            'contract' => '業務委託・アルバイト',
            'url'      => 'https://herp.careers/careers/companies/prointern/jobs/Qoqj7htSXpcI',
            'listed'   => '2026-10-13',
            'checked'  => '2026-10-13',
        ),
        array(
            'slug'     => 'herp-agent-sales-support',
            'company'  => '株式会社HERP',
            'title'    => '求人掲載サービスの営業サポート',
            'category' => '営業サポート',
            'summary'  => '採用企業に人材紹介会社向けの求人掲載サービスを案内し、メールと電話で求人の出稿まで進めます。アポ取りや初回の商談、出稿後のフォローは別の担当が行い、マニュアルに沿って進める仕事です。',
            'remote'   => '完全在宅・フルリモート（訪問なし。五反田のオフィスは使いたい人だけ）',
            'pay'      => '時給1,500円〜',
            'hours'    => '平日9〜19時のうち週4日以上・1日6時間〜（月100時間〜、相談可）・3か月以上。中抜け・副業OK',
            'target'   => '法人のお客様とのやりとりの経験があり、SalesforceとSlackを使ったことがある方',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/herpinc/jobs/QGNyx-wQr-Gu',
            'listed'   => '2026-10-16',
            'checked'  => '2026-10-16',
        ),
        array(
            'slug'     => 'herp-agent-sales',
            'company'  => '株式会社HERP',
            'title'    => '採用支援サービスのオンライン営業',
            'category' => 'オンライン営業',
            'summary'  => '採用企業に、人材紹介会社向けの求人掲載サービスと採用管理システムを提案します。商談はすべてオンラインで、アポ取り後のフォローは別の担当が行います。',
            'remote'   => '完全在宅・フルリモート（訪問なし。五反田のオフィスは使いたい人だけ）',
            'pay'      => '時給1,500円〜',
            'hours'    => '平日9〜19時のうち週4日以上・1日6時間〜（月100時間〜、相談可）・3か月以上。中抜け・副業OK',
            'target'   => '法人向けの新規提案営業（形のない商品）の経験が1年以上あり、SalesforceとSlackを使える方',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/herpinc/jobs/jKM_E9zsep81',
            'listed'   => '2026-10-16',
            'checked'  => '2026-10-16',
        ),
        array(
            'slug'     => 'herp-match-operator',
            'company'  => '株式会社HERP',
            'title'    => '人材紹介のマッチングオペレーター',
            'category' => '採用サポート',
            'summary'  => 'システムがすすめた求職者のリストを見て、求人の条件にどれだけ合うかを評価・選別します。結果をサービスの改善にも生かします。',
            'remote'   => 'フルリモート',
            'pay'      => '時給2,000円〜',
            'hours'    => '週5日・1日8時間程度（慣れたら時間帯の調整・夜間も可）',
            'target'   => '求人票や職務経歴書を読んで、求人と人が合うかを判断できる方（人材紹介・企業の人事の経験があると歓迎）',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/herpinc/jobs/LUF-tjQIAm-0',
            'listed'   => '2026-10-16',
            'checked'  => '2026-10-16',
        ),
        array(
            'slug'     => 'zapath-clinic-operator',
            'company'  => '株式会社zapath',
            'title'    => 'クリニックの患者さま対応オペレーター',
            'category' => 'カスタマーサポート',
            'summary'  => '提携クリニックの患者さまからの電話・LINE・メールに一次対応し、予約の受付や変更、問い合わせへの回答をします。AIと一緒に進める仕事です。',
            'remote'   => 'フルリモート（在宅勤務）。本社（恵比寿）での勤務は希望する人だけ',
            'pay'      => '時給1,400〜1,800円（パートの場合）',
            'hours'    => '8〜21時のうち1日4〜8時間のシフト制（土日祝を含む。中心は10〜19時）',
            'target'   => '電話・チャットなどでのお客様対応の経験が1年以上ある方',
            'contract' => 'パート・業務委託',
            'url'      => 'https://herp.careers/careers/companies/zapath/jobs/4TUAPffBHMC8',
            'listed'   => '2026-10-16',
            'checked'  => '2026-10-16',
        ),
        array(
            'slug'     => 'mimir-research-assistant',
            'company'  => 'ユーザベースグループ（MIMIR）',
            'title'    => 'リサーチ会社のリサーチアシスタント',
            'category' => 'リサーチ・事務アシスタント',
            'summary'  => 'コンサルティング会社などから依頼を受けるリサーチチームで、メールの送信やリサーチの補助などのサポートを担当します。コンサル担当者とのやりとりも一部あります。',
            'remote'   => '全国どこからでもフルリモート（丸の内オフィスに出社できる日もあるが任意で、強制なし）',
            'pay'      => '時給1,280円',
            'hours'    => '平日9〜18時の間で1日8時間・週5日',
            'target'   => '社員か契約社員として3年以上働いた経験があり、Excelを仕事で使ったことがある方',
            'contract' => 'アルバイト（業務委託も相談可）',
            'url'      => 'https://hrmos.co/pages/uzabase/jobs/MIMIR_008',
            'listed'   => '2026-10-16',
            'checked'  => '2026-10-22',
            'closed'   => true, // 2026-10-22 募集ページが削除されていた
        ),
        array(
            'slug'     => 'mimir-office-assistant',
            'company'  => 'ユーザベースグループ（MIMIR）',
            'title'    => 'リサーチ会社の一般事務',
            'category' => '事務・アシスタント',
            'summary'  => '事業会社から依頼を受けるリサーチチームで、メールの送信やリサーチ業務の補助など、サポート業務全般を担当します。',
            'remote'   => '全国どこからでもフルリモート（丸の内オフィスに出社できる日もあるが任意で、強制なし）',
            'pay'      => '時給（金額は募集ページで確認）',
            'hours'    => '平日9〜18時の間で1日8時間・週5日',
            'target'   => '社員か契約社員として3年以上働いた経験があり、Excelを仕事で使ったことがある方',
            'contract' => 'アルバイト',
            'url'      => 'https://hrmos.co/pages/uzabase/jobs/MIMIR_002',
            'listed'   => '2026-10-16',
            'checked'  => '2026-10-22',
            'closed'   => true, // 2026-10-22 募集ページが削除されていた
        ),
        array(
            'slug'     => 'upforward-assistant',
            'company'  => '株式会社UP Forward',
            'title'    => '人事コンサル会社の事務アシスタント',
            'category' => '事務・アシスタント',
            'summary'  => '人事制度のコンサルタントを支えるアシスタントです。PowerPoint・Excelでの資料づくり、議事録、データの入力・集計、採用の日程調整、請求書の発行などを担当します。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,800〜3,000円',
            'hours'    => '平日の週4日以上・1日4時間から（シフト制、時間は相談可）',
            'target'   => 'Word・Excel・PowerPointの基本操作とビジネスメールができる方。人事・総務・営業事務の経験があると歓迎',
            'contract' => 'アルバイト',
            'url'      => 'https://herp.careers/careers/companies/upforward/jobs/Wu9S_QOey-37',
            'listed'   => '2026-10-19',
            'checked'  => '2026-10-19',
        ),
        array(
            'slug'     => 'yurulica-recruiting-pr',
            'company'  => '株式会社ユルリカ',
            'title'    => 'Web制作・AI活用支援の会社の採用・広報マーケティング',
            'category' => 'マーケティング',
            'summary'  => '代表や現場メンバーと話しながら、会社の強みや仕事の魅力を言葉にし、採用・広報・自社マーケティングの施策を考えて実行します。',
            'remote'   => 'フルリモート',
            'pay'      => '時給2,500〜4,000円（税別）',
            'hours'    => '月60〜80時間程度',
            'target'   => 'マーケティング施策を考えて実行・改善した経験と、小さな会社での広報・マーケティングの経験がある方',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/yurulica/jobs/5DbOhwr8x4SS',
            'listed'   => '2026-10-19',
            'checked'  => '2026-10-19',
        ),
        array(
            'slug'     => 'gorlem-estimate-sorting',
            'company'  => '株式会社ゴーレム',
            'title'    => '建築見積書の仕分けスタッフ',
            'category' => '事務・データ確認',
            'summary'  => '建築の見積書の各行が、どの工事区分のどの資材なのかを確かめて、Excelで仕分ける事務作業です。',
            'remote'   => 'フルリモート',
            'pay'      => '時給2,000円',
            'hours'    => '週3日・1日4時間から（早朝・夜・土日の作業も可）',
            'target'   => '積算や長期修繕計画の業務経験が1年以上、建設業界の経験が2年以上あり、Excelで1,000行以上のデータを扱ったことがある方',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/gorlem/jobs/JPDPEhR0nAR7',
            'listed'   => '2026-10-19',
            'checked'  => '2026-10-19',
        ),
        array(
            'slug'     => 'mimosa-short-video-editor',
            'company'  => '株式会社Mimosa',
            'title'    => '採用・人事向けショート動画の編集',
            'category' => '動画制作',
            'summary'  => '人事・採用向けのショート動画サービスで、撮影済みの素材からSNS用のショート動画とサムネイルを編集します。',
            'remote'   => 'フルリモート',
            'pay'      => '動画1本3,500円（サムネイル込み・税込）',
            'hours'    => '本数に応じて（稼働時間の指定は募集ページに記載なし）',
            'target'   => 'TikTok・Instagram・YouTube Shortsいずれかのショート動画の編集経験が2年以上ある方',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/mimosa/jobs/cpFRsnXp7bn6',
            'listed'   => '2026-10-19',
            'checked'  => '2026-10-22',
            'closed'   => true, // 2026-10-22 募集ページが削除されていた
            'caution'  => '業務委託・1本ごとの報酬のため、時給の保証はありません。',
        ),
        array(
            'slug'     => 'she-career-planner',
            'company'  => 'SHE株式会社',
            'title'    => 'キャリアスクール「SHElikes」のキャリアプランナー',
            'category' => 'オンライン営業',
            'summary'  => '女性向けキャリアスクールの無料体験に参加した方とオンラインで面談し、入会の手続きや受講計画づくりをサポートします。',
            'remote'   => 'フルリモート',
            'pay'      => '1回2,500円＋成約に応じたインセンティブ',
            'hours'    => '月30回以上のシフト制（平日夜・土日中心か、平日中心かを選べる）',
            'target'   => '個人・法人を問わず営業経験があり、数値目標に向けて工夫した経験がある方',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/sheinc/jobs/lOWO-43M1YO7',
            'listed'   => '2026-10-19',
            'checked'  => '2026-10-19',
        ),
        array(
            'slug'     => 'merrybiz-remote-accounting',
            'company'  => 'メリービズ株式会社',
            'title'    => 'フルリモートの経理スタッフ',
            'category' => '事務・経理',
            'summary'  => '経理代行サービスのリモートスタッフとして、クライアント企業の経理業務をサポートします。育児・介護中の方や副業の方も多く働いています。',
            'remote'   => 'フルリモート（全国・海外在住の方も在籍）',
            'pay'      => '時給（金額は募集ページで確認）',
            'hours'    => '平日9〜19時の間で週3日以上・1日3時間以上・週15時間以上（月末月初は週4〜5日程度）',
            'target'   => '社会人経験が2年以上あり、経理の実務経験か簿記の資格がある方',
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2184948',
            'listed'   => '2026-10-19',
            'checked'  => '2026-10-19',
        ),
        array(
            'slug'     => 'wonder-education-line-cs',
            'company'  => '株式会社Wonder Education',
            'title'    => 'オンライン講座のLINE対応スタッフ',
            'category' => 'カスタマーサポート',
            'summary'  => 'オンライン講座に興味を持った方からの質問に返信し、学習の進め方や始める前の不安に答えます。慣れてきたら受講中の方への返信や数値の入力も担当します。',
            'remote'   => 'フルリモート',
            'pay'      => '報酬（金額は募集ページで確認）',
            'hours'    => '週4日以上・1日5時間から（8〜23時の間）',
            'target'   => '社会人経験が1年以上あり、ビジネスメールを書いたことがある方。カメラ付きのパソコンが必要',
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2522191',
            'listed'   => '2026-10-19',
            'checked'  => '2026-10-19',
        ),
        array(
            'slug'     => 'shiftai-counter-sales',
            'company'  => '株式会社SHIFT AI',
            'title'    => 'AI学習コミュニティのカウンターセールス',
            'category' => 'オンライン営業',
            'summary'  => 'AI活用コミュニティに問い合わせてくれた個人のお客様に、オンライン面談やチャットでサービスを案内します。飛び込みや電話での新規開拓はありません。',
            'remote'   => 'フルリモート（東京オフィスへの出社は希望者のみ）',
            'pay'      => '報酬（金額は募集ページで確認）',
            'hours'    => '週20時間以上（月80時間以上）',
            'target'   => '未経験から挑戦可。Slackなどのチャットツールやzoomを使える方',
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2547581',
            'listed'   => '2026-10-19',
            'checked'  => '2026-10-19',
        ),
        array(
            'slug'     => 'parasol-matching-planner',
            'company'  => '株式会社Parasol',
            'title'    => '紹介型婚活サービスのマッチングプランナー',
            'category' => '婚活の相談・マッチング',
            'summary'  => '婚活サービスの会員とオンラインで面談して人柄や希望を聞き、相性の良いお相手を探して紹介します。LINEでの婚活相談も担当します。',
            'remote'   => '完全フルリモート（全国・海外在住の方も在籍）',
            'pay'      => '報酬（金額は募集ページで確認）',
            'hours'    => '会員に合わせて平日夜・土日の昼が中心',
            'target'   => '副業で働きたい方も歓迎（プランナーの多くは20〜40代の副業の女性）',
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/1835557',
            'listed'   => '2026-10-19',
            'checked'  => '2026-10-19',
        ),
        array(
            'slug'     => 'tential-cs-shortterm',
            'company'  => '株式会社TENTIAL',
            'title'    => 'コンディショニング用品のメールカスタマーサポート（期間限定）',
            'category' => 'カスタマーサポート',
            'summary'  => '睡眠・リカバリー用品を買ったお客様からの問い合わせに、メールで答える仕事です。注文や配送の確認、返品・交換の案内など、手順の決まった問い合わせを担当します。最初の2週間ほどは研修があり、研修のあとも相談役の社員がつきます。',
            'remote'   => 'フルリモート（全国どこからでも）',
            'pay'      => '時給1,400円〜',
            'hours'    => '平日 週5日・9:45〜18:45（2026年11月上旬〜2027年1月末の期間限定）',
            'target'   => '基本的なパソコン操作（文字入力・メール・ブラウザ）ができる方。カスタマーサポートは未経験でも始められます',
            'contract' => '契約社員',
            'caution'  => '11月〜1月の3か月間の期間限定です。',
            'url'      => 'https://herp.careers/careers/companies/tential/jobs/xsVk5w6KKuAr',
            'listed'   => '2026-10-23',
            'checked'  => '2026-10-23',
        ),
        array(
            'slug'     => 'wellpartner-online-nurse',
            'company'  => '医療法人社団ウェルパートナー',
            'title'    => 'オンライン診療の看護師（診療補助）',
            'category' => '医療・看護',
            'summary'  => 'オンライン診療を行うクリニックで、診療の補助をする看護師の仕事です。面接もオンラインで行われます。',
            'remote'   => '完全在宅（全国どこからでも）',
            'pay'      => '時給1,600円',
            'hours'    => 'シフト制で8:00〜23:00の間（日〜月曜）。フルタイムや土日祝に入れる方を優先',
            'target'   => '正看護師または准看護師の資格がある方。オンライン診療の経験は問いません。パソコンで作業でき、自宅のネット環境が安定している方',
            'contract' => '業務委託',
            'url'      => 'https://herp.careers/careers/companies/wpclinic/jobs/H6sawcJxk7kz',
            'listed'   => '2026-10-23',
            'checked'  => '2026-10-23',
        ),
        array(
            'slug'     => 'fermelanta-ceo-office-intern',
            'company'  => 'ファーメランタ株式会社',
            'title'    => '研究開発スタートアップのCEO室インターン',
            'category' => '経営企画・事業開発',
            'summary'  => 'CEOの近くで、市場調査や会議資料づくり、データの整理と分析を担当します。生成AIを使った業務の効率化や社内ツールづくり、各種申請などの事務も任されます。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,300〜2,000円',
            'hours'    => '週2日以上・週10時間以上、平日9:00〜19:00の間（当面は土日なし）。6か月以上続けられる方',
            'target'   => '大学生・大学院生の長期インターン。生成AIを日常的に使っていて、資料づくりなど基本的なパソコン操作ができる方',
            'contract' => '長期インターン',
            'url'      => 'https://u-intern.com/fermelanta/',
            'listed'   => '2026-10-23',
            'checked'  => '2026-10-23',
        ),
        array(
            'slug'     => 'replus-ai-automation-intern',
            'company'  => 'Re;plus asia',
            'title'    => '広告代理店でAIを使った業務の自動化インターン',
            'category' => '事務・アシスタント',
            'summary'  => 'Meta広告の代理店で、ClaudeやChatGPTを使って請求書の処理や広告データの集計などを自動化する仕事です。エンジニアでなくても、AIを使って業務の仕組みをつくる経験ができます。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,250〜1,500円',
            'hours'    => '週9時間以上、10:00〜19:00の間（土日も可）',
            'target'   => '大学生・大学院生の長期インターン。AIツールを自分で試した経験があり、GitHubの基本操作ができる方',
            'contract' => '長期インターン',
            'url'      => 'https://u-intern.com/replusasia/',
            'listed'   => '2026-10-23',
            'checked'  => '2026-10-23',
        ),
        array(
            'slug'     => 'arcra-ai-engineer-intern',
            'company'  => '株式会社ARCRA',
            'title'    => 'AIスタートアップのエンジニアインターン（Web・LLM・画像処理）',
            'category' => 'エンジニア（AI）',
            'summary'  => '東大松尾研発のスタートアップで、Next.jsやAWSを使ったWeb開発、RAGやファインチューニングを使ったAIエージェント開発、画像認識・生成のどれかを担当します。',
            'remote'   => 'フルリモート',
            'pay'      => '時給2,000円〜',
            'hours'    => '週15時間以上（24時間いつでも、土日も可）',
            'target'   => '大学生・大学院生の長期インターン。エンジニアとして実務経験を積みたい方（実務経験があれば歓迎）',
            'contract' => '業務委託',
            'url'      => 'https://u-intern.com/arcra2/',
            'listed'   => '2026-10-23',
            'checked'  => '2026-10-23',
        ),
        array(
            'slug'     => 'andgo-software-engineer-intern',
            'company'  => '株式会社AndGo',
            'title'    => 'AI開発支援プロダクトのエンジニア・PMインターン',
            'category' => 'エンジニア',
            'summary'  => '元大学教員のCEOのもとで、AI時代のソフトウェア開発を支える新しい製品の試作を担当します。プログラムを書いて技術的にできるかを確かめたり、仕様書づくりを手伝ったりします。',
            'remote'   => 'フルリモート',
            'pay'      => '時給1,500〜3,000円',
            'hours'    => '週12時間以上、8:00〜22:00の間（土日も可）',
            'target'   => '大学生・大学院生の長期インターン。TypeScriptでバックエンドを開発できる方',
            'contract' => '業務委託',
            'url'      => 'https://u-intern.com/andgo/',
            'listed'   => '2026-10-23',
            'checked'  => '2026-10-23',
        ),
        array(
            'slug'     => 'laugh-ceo-office-intern',
            'company'  => 'ラフ株式会社',
            'title'    => '大人の趣味のオンライン講座会社で、代表直下の経営企画・マーケティングインターン',
            'category' => '経営企画・事業開発',
            'summary'  => '刺繍やレザークラフトなどのオンライン講座をつくって売る会社で、代表のすぐ近くで働きます。会議資料や新しい講座の市場調査、広告やLPの改善、生成AIを使った社内作業の自動化などを、本人の希望に合わせて担当します。',
            'remote'   => 'フルリモート（全国どこからでも）',
            'pay'      => '時給1,500円',
            'hours'    => '週3日・週12時間以上、9:00〜22:00の間（土日も可）。6か月以上続けられる方',
            'target'   => '大学生・大学院生の長期インターン。生成AI（ChatGPTやClaudeなど）を自分の作業で使ったことがある方',
            'url'      => 'https://u-intern.com/laugh3/',
            'listed'   => '2026-10-29',
            'checked'  => '2026-10-29',
        ),
        array(
            'slug'     => 'lifeprompt-engineer-intern',
            'company'  => '株式会社LifePrompt',
            'title'    => '生成AIプロダクトのエンジニアインターン（Next.js）',
            'category' => 'エンジニア',
            'summary'  => '企業向けの生成AIプロダクトを開発するスタートアップで、画面からサーバーまでの設計と開発を担当します。AIツールを使いながら、PMやデザイナーと相談して機能をつくります。',
            'remote'   => 'フルリモート',
            'pay'      => '時給2,000〜4,000円（未経験の場合は1,300円から）',
            'hours'    => '週3回以上・週20時間以上が目安。時間帯は自由（土日祝も可）',
            'target'   => '大学生・大学院生の長期インターン。何らかのプログラミング言語での開発経験と、Next.jsやNode.jsの基礎知識、GitHubを使った開発経験がある方',
            'contract' => '業務委託',
            'url'      => 'https://ut-board.com/recruitment/693/',
            'listed'   => '2026-10-29',
            'checked'  => '2026-10-29',
        ),
        array(
            'slug'     => 'mediafirst-backoffice',
            'company'  => '株式会社メディアファースト',
            'title'    => 'Web広告代理店のバックオフィス（チャット対応・データ入力から）',
            'category' => '事務・アシスタント',
            'summary'  => 'オフィスを持たず、全員が在宅で働くWeb広告代理店で、チャットやメールでのお客様対応（ひな形あり）や簡単なデータ入力から始めます。慣れてきたら、マーケティングの補助や経理、採用の手伝いにも広げられます。',
            'remote'   => '完全在宅（オフィスなし・全国どこからでも）',
            'pay'      => '',
            'hours'    => '週15時間以上、曜日・時間帯は自由（土日・早朝・深夜も可）',
            'target'   => '未経験から始められます。パソコン・ネット環境・スマートフォンがある方',
            'contract' => '業務委託',
            'url'      => 'https://www.wantedly.com/projects/2060682',
            'listed'   => '2026-10-29',
            'checked'  => '2026-10-29',
        ),
        array(
            'slug'     => 'stocksun-hr-marketing-intern',
            'company'  => 'StockSun株式会社',
            'title'    => '生成AIを使った採用支援・マーケティングのアシスタント',
            'category' => 'マーケティング',
            'summary'  => '生成AIで採用支援や資料づくりをする新しい事業で、提案資料やホワイトペーパーの作成、お客様企業の求人原稿づくりやデータ分析を手伝います。オンライン会議に同席して議事録をとる仕事もあります。',
            'remote'   => '完全フルリモート。地方在住OK',
            'pay'      => '',
            'hours'    => '週5時間から相談可',
            'target'   => '大学生・大学院生（既卒も可）。スプレッドシートやチャットツールなど、パソコンの基本操作ができる方',
            'contract' => 'インターン',
            'url'      => 'https://www.wantedly.com/projects/2052201',
            'listed'   => '2026-10-29',
            'checked'  => '2026-10-29',
        ),
        array(
            'slug'     => 'yoake-engineer-intern',
            'company'  => 'yoake株式会社',
            'title'    => 'AI就活支援サービスのエンジニアインターン（React・Node.js）',
            'category' => 'エンジニア',
            'summary'  => 'AIを使った就活支援サービスの開発を担当します。ReactやNode.jsでの実装と、生成AIを組み込む部分の開発をします。',
            'remote'   => '完全リモート',
            'pay'      => '',
            'hours'    => '時間は柔軟（週20時間以上歓迎）、3か月以上',
            'target'   => '学生。何らかの言語での開発経験がある方（授業や独学でも可）',
            'contract' => 'インターン',
            'url'      => 'https://www.wantedly.com/projects/2518325',
            'listed'   => '2026-10-29',
            'checked'  => '2026-10-29',
        ),
    );
    // 掲載基準（初日・研修・会議を含めて出社を求めない）に合わないと分かった求人は、記録だけ残して表に出さない。
    $jobs = array_values( array_filter( $jobs, function ( $j ) {
        return empty( $j['excluded'] );
    } ) );
    // 企業がzaitoに直接掲載した求人（inc/direct.php）を混ぜる。
    return apply_filters( 'zaito_remote_jobs', $jobs );
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
    // 直接掲載の求人の応募フォーム（inc/direct.php・lp/direct-apply.php）。
    add_rewrite_rule( '^remote/(d[0-9]+)/apply/?$', 'index.php?zaito_page=remote&zaito_remote=$matches[1]&zaito_apply=1', 'top' );
}
add_action( 'init', 'zaito_remote_rewrite_rules' );

function zaito_remote_query_vars( $vars ) {
    $vars[] = 'zaito_remote';
    $vars[] = 'zaito_go';
    $vars[] = 'zaito_apply';
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

    if ( get_query_var( 'zaito_apply' ) && ! empty( $job['direct'] ) ) {
        $zaito_remote_job = $job;
        nocache_headers();
        status_header( 200 );
        header( 'Content-Type: text/html; charset=UTF-8' );
        header( 'X-Robots-Tag: noindex, nofollow' );
        include get_template_directory() . '/lp/direct-apply.php';
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
    // 直接掲載の求人は、zaitoの中の応募フォームへ。
    if ( ! empty( $job['direct'] ) ) {
        wp_safe_redirect( $job['url'], 302 );
        return;
    }
    // 転送先は zaito_remote_jobs() に運営が書いたURLだけ（利用者の入力は使わない）。
    wp_redirect( esc_url_raw( zaito_remote_out_url( $job ) ), 302 );
}

/**
 * 応募ページへのURLに、zaitoから来たことが分かる印（utm）を付ける。
 * 掲載先の会社がアクセス解析で「zaito経由の応募」を数えられるようにするため。
 * もとのURLにすでに utm_source があるときは、会社が決めた印を優先してそのままにする。
 */
function zaito_remote_out_url( $job ) {
    $url = $job['url'];
    if ( false !== strpos( $url, 'utm_source=' ) ) {
        return $url;
    }
    return add_query_arg( array(
        'utm_source'   => 'zaito',
        'utm_medium'   => 'referral',
        'utm_campaign' => 'zaito_job',
        'utm_content'  => $job['slug'],
    ), $url );
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
    // 新着メールから来た訪問なら、その配信の成果として数える（inc/newsletter.php）。
    do_action( 'zaito_remote_click', $slug, $member );

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
    // 大学のWi-Fiやスマホ回線では大勢が同じIPを使うため、ふつうの登録が止まらないよう多めに許す。
    if ( zaito_lp_rate_limited( 'remote', 30 ) ) {
        $fail( '送信回数が多すぎます。しばらくしてからお試しください' );
    }

    // 登録元: 求人詳細の応募ボタンからなら remote、トップページの新着登録からなら top。
    $result = zaito_lp_find_or_create_interest( $email, $job ? 'remote' : 'top' );
    if ( ! $result ) {
        $fail( '登録に失敗しました。時間をおいて再度お試しください' );
    }
    list( $interest_id, $created ) = $result;
    if ( $created ) {
        zaito_lp_send_waitlist_thanks( $email, 'remote', $interest_id );
    }
    zaito_remote_set_member_cookie( $interest_id );
    // 新しく登録したブラウザは本人とみなし、マイページを使えるようにする（inc/mypage.php）。
    do_action( 'zaito_remote_registered', $interest_id, $created );

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
        'days'      => array( '週1〜2日', '週3〜4日', '週5日' ),
        'min_pay'   => array( 'こだわらない', '時給1,200円以上', '時給1,500円以上', '時給2,000円以上' ),
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
    if ( zaito_lp_rate_limited( 'remote_profile', 30 ) ) {
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
    foreach ( array( 'days', 'min_pay' ) as $key ) {
        $val = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
        if ( in_array( $val, $opts[ $key ], true ) ) {
            update_post_meta( $member, $key, $val );
        }
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
    $favs    = get_option( 'zaito_fav_stats', array() );
    $favs    = is_array( $favs ) ? $favs : array();
    $applies = function_exists( 'zaito_ac_counts' ) ? zaito_ac_counts() : array();
    $fav_now = 0;
    foreach ( $favs as $f ) {
        $fav_now += count( $f['saved'] );
    }
    ?>
    <div class="wrap">
      <h1>まとめ求人</h1>
      <p>公開ページ: <a href="<?php echo esc_url( zaito_remote_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( zaito_remote_url() ); ?></a>　｜　登録している学生（先行登録を含む）: <strong><?php echo esc_html( $members ); ?>人</strong></p>
      <p>「進んだ学生」は、登録して元の応募ページへ進んだ学生の人数です（同じ学生が何度押しても1人）。企業への営業では、この人数を伝えます。求人の追加・修正は <code>inc/remote-jobs.php</code> で行います。</p>
      <p>「保存中」は、いまその求人を保存しているブラウザの数です（登録していない人も含む。同じブラウザは1回）。「保存された回数」は保存ボタンが押された回数の合計で、外した分は引いていません。いま保存されている数の合計: <strong><?php echo esc_html( $fav_now ); ?></strong></p>
      <p>「応募した」「採用」は、応募ページへ進んだ学生に、2日後と21日後にメールで聞いた答えの人数です（inc/apply-track.php）。答えた人だけを数えるので、実際より少なめに出ます。</p>
      <?php
      // 企業ごとの合計（営業・報告用）
      $by_co = array();
      foreach ( zaito_remote_jobs() as $job ) {
          $co = $job['company'];
          $s  = isset( $stats[ $job['slug'] ] ) ? $stats[ $job['slug'] ] : array( 'members' => array() );
          $a  = isset( $applies[ $job['slug'] ] ) ? $applies[ $job['slug'] ] : array( 'applied' => 0, 'hired' => 0 );
          if ( ! isset( $by_co[ $co ] ) ) {
              $by_co[ $co ] = array( 'go' => 0, 'applied' => 0, 'hired' => 0 );
          }
          $by_co[ $co ]['go']      += count( $s['members'] );
          $by_co[ $co ]['applied'] += $a['applied'];
          $by_co[ $co ]['hired']   += $a['hired'];
      }
      $by_co = array_filter( $by_co, function ( $c ) {
          return $c['go'] || $c['applied'];
      } );
      uasort( $by_co, function ( $x, $y ) {
          return ( $y['applied'] - $x['applied'] ) ?: ( $y['go'] - $x['go'] );
      } );
      ?>
      <?php if ( $by_co ) : ?>
        <h2>企業ごとの合計（報告用）</h2>
        <table class="widefat striped" style="max-width:720px;margin-bottom:24px">
          <thead><tr><th>企業</th><th>応募ページへ進んだ学生</th><th>応募したと答えた学生</th><th>採用されたと答えた学生</th></tr></thead>
          <tbody>
          <?php foreach ( $by_co as $co => $c ) : ?>
            <tr><td><?php echo esc_html( $co ); ?></td><td><?php echo esc_html( $c['go'] ); ?>人</td><td><strong><?php echo esc_html( $c['applied'] ); ?></strong>人</td><td><strong><?php echo esc_html( $c['hired'] ); ?></strong>人</td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      <table class="widefat striped">
        <thead><tr><th>企業・求人</th><th style="width:110px">進んだ学生</th><th style="width:90px">応募した</th><th style="width:70px">採用</th><th style="width:90px">保存中</th><th style="width:100px">保存された回数</th><th style="width:110px">クリック数</th><th style="width:150px">最後のクリック</th><th style="width:110px">確認日</th><th>元のページ</th></tr></thead>
        <tbody>
        <?php foreach ( zaito_remote_jobs() as $job ) :
            $s = isset( $stats[ $job['slug'] ] ) ? $stats[ $job['slug'] ] : array( 'clicks' => 0, 'members' => array(), 'last' => '' );
        ?>
          <tr>
            <td><strong><?php echo esc_html( $job['company'] ); ?></strong><br><a href="<?php echo esc_url( zaito_remote_url( $job['slug'] ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $job['title'] ); ?></a><?php echo ! empty( $job['closed'] ) ? '（募集終了）' : ''; ?></td>
            <td><strong style="font-size:16px"><?php echo esc_html( count( $s['members'] ) ); ?></strong>人</td>
            <?php $a = isset( $applies[ $job['slug'] ] ) ? $applies[ $job['slug'] ] : array( 'applied' => 0, 'hired' => 0 ); ?>
            <td><?php echo esc_html( $a['applied'] ); ?>人</td>
            <td><?php echo esc_html( $a['hired'] ); ?>人</td>
            <td><?php echo esc_html( isset( $favs[ $job['slug'] ] ) ? count( $favs[ $job['slug'] ]['saved'] ) : 0 ); ?></td>
            <td><?php echo esc_html( isset( $favs[ $job['slug'] ] ) ? (int) $favs[ $job['slug'] ]['adds'] : 0 ); ?>回</td>
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
