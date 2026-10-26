<?php
/**
 * 「今日のおすすめ」を生成AI（Anthropic の Claude）で選ぶ。inc/reco.php から呼ぶ。
 *
 * - 掲載中の求人の一覧（職種・タイトル・時給・時間・対象など、公開している情報だけ）と、
 *   登録した希望条件（立場・学年・やってみたい仕事・日数・希望時給）を渡し、おすすめ順に15件と理由を返させる。
 *   名前・メールアドレス・学校名は送らない。
 * - 結果は1日ごとに保存し、同じ人には1日1回しか呼ばない（登録していない人向けは全員で1日1回）。
 *   希望条件や求人が変わったら選び直す。
 * - APIキーは public_html の外の zaito-secrets.php（デプロイのときに GitHub の秘密情報から作る）で定義する。
 *   キーがない・AIが答えない・時間がかかりすぎたときは null を返し、inc/reco.php の点数づけで選ぶ。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ZAITO_RECO_AI_MODEL', 'claude-opus-5-5' );
define( 'ZAITO_RECO_AI_POOL', 15 );

function zaito_reco_ai_key() {
    $secrets = dirname( ABSPATH ) . '/zaito-secrets.php';
    if ( ! defined( 'ZAITO_ANTHROPIC_API_KEY' ) && is_readable( $secrets ) ) {
        require_once $secrets;
    }
    return defined( 'ZAITO_ANTHROPIC_API_KEY' ) ? (string) ZAITO_ANTHROPIC_API_KEY : '';
}

function zaito_reco_ai_client() {
    $autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
    $key      = zaito_reco_ai_key();
    if ( '' === $key || ! is_readable( $autoload ) ) {
        return null;
    }
    require_once $autoload;
    return new \Anthropic\Client(
        apiKey: $key,
        requestOptions: array(
            // 待ち時間の上限は通信の側（Guzzle）で決まる。ページの表示を止めすぎないよう短めにする。
            'transporter' => new \GuzzleHttp\Client( array( 'timeout' => 25, 'connect_timeout' => 5 ) ),
            'maxRetries'  => 1,
        ),
    );
}

/**
 * AIに渡す求人の一覧（1件1行）。公開している情報だけ。
 */
function zaito_reco_ai_catalog( $jobs ) {
    $lines = array();
    foreach ( $jobs as $j ) {
        $tags = array();
        if ( preg_match( '/学生|大学|全学年/u', $j['target'] ) ) {
            $tags[] = '学生OK';
        }
        if ( zaito_remote_has_tag( $j, 'mikeiken' ) || preg_match( '/未経験|経験不問|経験は不問/u', $j['target'] ) ) {
            $tags[] = '未経験OK';
        }
        if ( ! empty( $j['listed'] ) && ( time() - strtotime( $j['listed'] ) ) <= 7 * DAY_IN_SECONDS ) {
            $tags[] = '新着';
        }
        $lines[] = implode( ' | ', array(
            $j['slug'],
            $j['category'],
            $j['title'],
            '' !== $j['pay'] ? $j['pay'] : '報酬は募集ページで確認',
            $j['hours'],
            '対象: ' . $j['target'],
            isset( $j['contract'] ) ? $j['contract'] : '',
            implode( '・', $tags ),
        ) );
    }
    return implode( "\n", $lines );
}

/**
 * AIでおすすめ順の求人（最大15件）と理由を返す。失敗したら null。
 * $p: 希望条件（null なら登録していない人向けの共通のおすすめ）。
 *
 * @return array<int, array{slug: string, why: string[]}>|null
 */
function zaito_reco_ai_rank( $jobs, $p ) {
    $prefs = null;
    if ( $p ) {
        $prefs = array(
            '立場'           => $p['role'],
            '学年'           => isset( $p['grade'] ) ? $p['grade'] : '',
            'やってみたい仕事' => implode( '、', $p['interests'] ),
            '働ける日数'      => $p['days'],
            '希望時給'        => $p['min_pay'],
        );
    }
    $slugs = array_map( function ( $j ) {
        return $j['slug'];
    }, $jobs );
    $cache = 'zaito_reco_ai_' . md5( wp_json_encode( array( current_time( 'Y-m-d' ), $prefs, $slugs ) ) );
    $hit   = get_transient( $cache );
    if ( is_array( $hit ) ) {
        return $hit;
    }
    // 同じ内容をいくつものリクエストが同時にAIへ問い合わせないようにする。
    if ( get_transient( $cache . '_lock' ) ) {
        return null;
    }
    $client = zaito_reco_ai_client();
    if ( ! $client ) {
        return null;
    }
    set_transient( $cache . '_lock', 1, 60 );

    $system = "あなたは、完全在宅（研修や会議も含めて出社なし）の求人だけを載せている求人サイト「zaito」で、利用者に合う求人を選ぶ担当です。\n"
        . "下の求人一覧から、利用者に合う順に" . ZAITO_RECO_AI_POOL . "件を選んでください。\n"
        . "- 応募できない求人（学生なのに学生が対象でない、資格が必須など）は選ばない。\n"
        . "- 希望時給より低い求人や、働ける日数を大きく超える求人は下げる。\n"
        . "- 同じ職種や同じ会社ばかりにならないようにする。\n"
        . "- reasons は、その人にとっての理由を1〜2個、それぞれ12文字以内の短い日本語で書く（例:「SNS運用に興味あり」「週1〜2日でOK」「未経験OK」）。誇張しない。\n"
        . "- slug は一覧の1列目をそのまま使う。\n\n"
        . "求人一覧（slug | 職種 | タイトル | 報酬 | 働く時間 | 対象 | 契約 | 印）:\n"
        . zaito_reco_ai_catalog( $jobs );

    $user = $prefs
        ? "利用者が登録した希望条件:\n" . wp_json_encode( $prefs, JSON_UNESCAPED_UNICODE )
        : "利用者はまだ希望条件を登録していません。初めてサイトに来た人（多くは在宅でアルバイトや長期インターンを探す大学生）に、まず見てほしい求人を選んでください。未経験から始めやすく、時給や条件が良いものを優先してください。";

    try {
        $message = $client->beta->messages->create(
            model: ZAITO_RECO_AI_MODEL,
            maxTokens: 4000,
            // 一覧は全員で同じなので、キャッシュして2回目以降を安く速くする。
            system: array(
                array( 'type' => 'text', 'text' => $system, 'cacheControl' => array( 'type' => 'ephemeral' ) ),
            ),
            messages: array( array( 'role' => 'user', 'content' => $user ) ),
            outputConfig: array(
                'effort' => 'low',
                'format' => array(
                    'type'   => 'json_schema',
                    'schema' => array(
                        'type'                 => 'object',
                        'properties'           => array(
                            'picks' => array(
                                'type'  => 'array',
                                'items' => array(
                                    'type'                 => 'object',
                                    'properties'           => array(
                                        'slug'    => array( 'type' => 'string' ),
                                        'reasons' => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
                                    ),
                                    'required'             => array( 'slug', 'reasons' ),
                                    'additionalProperties' => false,
                                ),
                            ),
                        ),
                        'required'             => array( 'picks' ),
                        'additionalProperties' => false,
                    ),
                ),
            ),
            // 安全のための判定で断られたときは、別のモデルで答え直してもらう。
            fallbacks: 'default',
            betas: array( 'server-side-fallback-2026-07-01' ),
        );
    } catch ( \Throwable $e ) {
        delete_transient( $cache . '_lock' );
        error_log( 'zaito reco ai: ' . get_class( $e ) . ' ' . substr( $e->getMessage(), 0, 200 ) );
        return null;
    }
    delete_transient( $cache . '_lock' );
    if ( 'refusal' === ( is_object( $message->stopReason ?? null ) ? $message->stopReason->value : ( $message->stopReason ?? '' ) ) ) {
        return null;
    }
    $data = null;
    foreach ( $message->content as $block ) {
        if ( 'text' === $block->type ) {
            $data = json_decode( $block->text, true );
            break;
        }
    }
    if ( ! is_array( $data ) || empty( $data['picks'] ) || ! is_array( $data['picks'] ) ) {
        return null;
    }
    $out = array();
    foreach ( $data['picks'] as $pk ) {
        $slug = isset( $pk['slug'] ) ? (string) $pk['slug'] : '';
        if ( ! in_array( $slug, $slugs, true ) || isset( $out[ $slug ] ) ) {
            continue;
        }
        $why = array();
        foreach ( (array) ( $pk['reasons'] ?? array() ) as $r ) {
            $r = trim( wp_strip_all_tags( (string) $r ) );
            if ( '' !== $r ) {
                $why[] = mb_substr( $r, 0, 14 );
            }
        }
        $out[ $slug ] = array( 'slug' => $slug, 'why' => array_slice( $why, 0, 2 ) );
    }
    if ( ! $out ) {
        return null;
    }
    $out = array_values( $out );
    set_transient( $cache, $out, DAY_IN_SECONDS );
    return $out;
}
