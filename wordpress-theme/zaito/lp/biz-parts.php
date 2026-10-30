<?php
/**
 * 企業の画面（page-company-*.php）と、メッセージ（page-chat.php）・応募フォーム（lp/direct-apply.php）で共通の部品。
 * 見た目は lp/tokens.css・remote.css・app.css。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/remote-parts.php';

if ( ! function_exists( 'zaito_biz_head' ) ) {
    /** <head> まで。検索結果には出さない。 */
    function zaito_biz_head( $title ) {
        nocache_headers();
        ob_start();
        zaito_remote_head( $title . ' | zaito', 'zaitoの企業向けページ', home_url( add_query_arg( array() ) ) );
        $head = ob_get_clean();
        $extra = '<meta name="robots" content="noindex, nofollow">' . "\n"
            . '<link rel="stylesheet" href="' . esc_url( zaito_lp_asset( 'app.css' ) ) . '">' . "\n</head>";
        echo str_replace( '</head>', $extra, $head ); // zaito_remote_head の出力（エスケープ済み）に、固定のタグを足すだけ
    }

    /**
     * 企業の画面の上部（ロゴ・会社名・ログアウトと、ページの切り替え）。$current は今のページのキー。
     */
    function zaito_biz_header( $current = '' ) {
        $user  = wp_get_current_user();
        $count = zaito_biz_counts( $user->ID );
        $items = array(
            'home'       => array( '/company/', 'home', 'ホーム', 0 ),
            'jobs'       => array( '/company-jobs/', 'work', '求人', 0 ),
            'applicants' => array( '/company-applicants/', 'group', '応募者', $count['unread'] ),
            'profile'    => array( '/company-profile/', 'business', '会社情報', 0 ),
        );
        ?>
<header class="biz-hd"><div class="wrap">
  <a class="logo" href="<?php echo esc_url( home_url( '/company/' ) ); ?>" aria-label="zaito 企業向けホームへ">za<span>i</span>to</a>
  <span class="for">企業の方</span>
  <div class="who"><span class="nm"><b><?php echo esc_html( zaito_company_name( $user->ID ) ); ?></b> 様</span>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><span class="ms" aria-hidden="true">open_in_new</span>サイトを見る</a>
    <a href="<?php echo esc_url( wp_logout_url( home_url( '/company-login/' ) ) ); ?>"><span class="ms" aria-hidden="true">logout</span>ログアウト</a></div>
</div></header>
<nav class="biz-nav" aria-label="企業向けメニュー"><div class="wrap">
  <?php foreach ( $items as $key => $it ) : ?>
    <a href="<?php echo esc_url( home_url( $it[0] ) ); ?>"<?php echo $key === $current ? ' aria-current="page"' : ''; ?>><span class="ms" aria-hidden="true"><?php echo esc_html( $it[1] ); ?></span><?php echo esc_html( $it[2] ); ?><?php if ( $it[3] ) : ?><span class="n" aria-label="未読 <?php echo (int) $it[3]; ?>件"><?php echo (int) $it[3]; ?></span><?php endif; ?></a>
  <?php endforeach; ?>
</div></nav>
        <?php
    }

    /**
     * 企業向けのログイン・登録・パスワード再設定の画面の上部。学生向けのメニューは出さず、
     * ロゴと「採用担当者様向け」と、ログイン⇔新規登録の切り替えだけにする。$mode は 'login' か 'register'。
     */
    function zaito_biz_auth_header( $mode = 'login' ) {
        ?>
<header class="biz-hd"><div class="wrap">
  <a class="logo" href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>" aria-label="zaito 企業の方へ">za<span>i</span>to</a>
  <span class="for">採用担当者様向け</span>
  <div class="who">
    <?php if ( 'register' === $mode ) : ?>
      <span class="nm">アカウントをお持ちの方</span><a class="btn btn-g" style="height:38px;color:var(--z-navy)" href="<?php echo esc_url( home_url( '/company-login/' ) ); ?>">ログイン</a>
    <?php else : ?>
      <a class="btn btn-p" style="height:38px;color:#fff" href="<?php echo esc_url( home_url( '/company-register/' ) ); ?>">無料で掲載をはじめる</a>
    <?php endif; ?>
  </div>
</div></header>
        <?php
    }

    /** 企業向けの認証画面の下部（運営者・規約・求職者向けサイトへのリンクだけ）。 */
    function zaito_biz_auth_footer() {
        ?>
<footer class="biz-auth-ft"><div class="wrap">
  <nav><a href="<?php echo esc_url( home_url( '/for-companies/' ) ); ?>">zaitoについて（企業の方へ）</a><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a><a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a><a href="mailto:info@zaito-work.com">お問い合わせ</a></nav>
  <p>求人を探している方は <a href="<?php echo esc_url( home_url( '/' ) ); ?>">こちら</a>　／　© zaito</p>
</div></footer>
        <?php
    }

    /** 企業のホームとメニューに出す数（未読のやりとり・新着の応募・掲載中・審査待ち）。 */
    function zaito_biz_counts( $user_id ) {
        static $cache = array();
        if ( isset( $cache[ $user_id ] ) ) {
            return $cache[ $user_id ];
        }
        $c = array( 'unread' => 0, 'new' => 0, 'open' => 0, 'pending' => 0, 'apps' => 0 );
        foreach ( zaito_direct_applications( 'company', $user_id ) as $a ) {
            $c['apps']++;
            if ( get_post_meta( $a->ID, 'unread_company', true ) ) {
                $c['unread']++;
            }
            if ( 'new' === zaito_direct_app_status( $a->ID ) ) {
                $c['new']++;
            }
        }
        foreach ( zaito_biz_jobs( $user_id ) as $p ) {
            $s = zaito_direct_job_state( $p );
            if ( 'open' === $s[0] ) {
                $c['open']++;
            } elseif ( 'pending' === $s[0] ) {
                $c['pending']++;
            }
        }
        $cache[ $user_id ] = $c;
        return $c;
    }

    function zaito_biz_jobs( $user_id ) {
        return get_posts( array(
            'post_type'      => 'job_listing',
            'post_status'    => array( 'publish', 'draft', 'pending' ),
            'posts_per_page' => 200,
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'meta_query'     => array(
                array( 'key' => '_company_user_id', 'value' => (int) $user_id ),
                array( 'key' => '_zaito_direct', 'value' => '1' ),
            ),
        ) );
    }

    /** アカウントの状態のお知らせ（メール確認・審査中・停止中）。 */
    function zaito_biz_account_notes( $user_id ) {
        $st = zaito_company_status( $user_id );
        if ( 'suspended' === $st ) {
            echo '<div class="note n-err"><span class="ms" aria-hidden="true">block</span><div><b>アカウントを停止しています</b><p>求人は公開されず、メッセージも送れません。くわしくは info@zaito-work.com までお問い合わせください。</p></div></div>';
        } elseif ( 'pending' === $st ) {
            echo '<div class="note n-info"><span class="ms" aria-hidden="true">pending</span><div><b>アカウントの審査中です（通常1〜2営業日）</b><p>アカウントの審査中でも、求人を作って審査に出せます。アカウントと求人の両方の確認が終わると、メールでお知らせし、求人を公開します。</p></div></div>';
        }
        if ( ! zaito_is_email_verified( $user_id ) ) {
            echo '<div class="note n-warn"><span class="ms" aria-hidden="true">mail</span><div><b>メールアドレスの確認をお願いします</b><p>登録したアドレスに届いたメールのリンクを押してください。</p>';
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="zaito_resend_verification">';
            wp_nonce_field( 'zaito_resend_verification' );
            echo '<button type="submit" class="btn btn-g">確認メールを送り直す</button></form></div></div>';
        }
    }

    /** フォームのエラー（zaito_store_form_errors_and_redirect で渡されたもの）。 */
    function zaito_biz_errors() {
        $errors = zaito_get_form_errors_from_token();
        if ( $errors ) {
            echo '<div class="note n-err" role="alert"><span class="ms" aria-hidden="true">warning</span><div><b>入力内容を確認してください</b><ul>';
            foreach ( $errors as $e ) {
                echo '<li>' . esc_html( $e ) . '</li>';
            }
            echo '</ul></div></div>';
        }
        return $errors;
    }

    function zaito_biz_footer() {
        ?>
<footer class="ft" style="margin-top:0"><div class="wrap"><div class="ft-b"><span>zaito 運営事務局　<a href="mailto:info@zaito-work.com">info@zaito-work.com</a></span><small><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">利用規約</a>　<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">プライバシーポリシー</a></small></div></div></footer>
        <?php
    }

    function zaito_biz_status_pill( $key, $label ) {
        return '<span class="st st-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</span>';
    }
}
