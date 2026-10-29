<?php
/**
 * 直接掲載: 運営の管理画面（WordPress管理画面「直接掲載」）。
 * 企業の承認・求人の審査・応募のようす・内定（成果報酬）の記録・設定をタブで切り替える。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function zaito_direct_admin_menu() {
    $pending = zaito_direct_admin_counts();
    $badge   = $pending['todo'] ? ' <span class="awaiting-mod">' . (int) $pending['todo'] . '</span>' : '';
    add_menu_page( '直接掲載', '直接掲載' . $badge, 'manage_options', 'zaito-direct', 'zaito_direct_admin_page', 'dashicons-groups', 4 );
}
add_action( 'admin_menu', 'zaito_direct_admin_menu' );

function zaito_direct_admin_counts() {
    static $c = null;
    if ( null !== $c ) {
        return $c;
    }
    $companies = get_users( array( 'role' => 'zaito_company', 'fields' => 'ID' ) );
    $c         = array( 'companies' => 0, 'jobs' => 0, 'stale' => 0, 'offers' => 0 );
    foreach ( $companies as $uid ) {
        if ( 'pending' === zaito_company_status( $uid ) ) {
            $c['companies']++;
        }
    }
    $c['jobs'] = count( get_posts( array(
        'post_type'      => 'job_listing',
        'post_status'    => 'any',
        'posts_per_page' => 200,
        'fields'         => 'ids',
        'meta_query'     => array( array( 'key' => '_zaito_review', 'value' => 'pending' ) ),
    ) ) );
    foreach ( zaito_direct_applications( 'all' ) as $a ) {
        if ( zaito_direct_is_stale( $a ) ) {
            $c['stale']++;
        }
        if ( 'unbilled' === get_post_meta( $a->ID, 'fee_status', true ) ) {
            $c['offers']++;
        }
    }
    $c['todo'] = $c['companies'] + $c['jobs'];
    return $c;
}

/** 企業がまだ一度も返信しておらず、応募から3日以上たっている応募。 */
function zaito_direct_is_stale( $app ) {
    return ! get_post_meta( $app->ID, 'first_reply_at', true )
        && in_array( zaito_direct_app_status( $app->ID ), array( 'new' ), true )
        && strtotime( $app->post_date ) < current_time( 'timestamp' ) - 3 * DAY_IN_SECONDS;
}

function zaito_direct_admin_url( $tab, $args = array() ) {
    return add_query_arg( array_merge( array( 'page' => 'zaito-direct', 'tab' => $tab ), $args ), admin_url( 'admin.php' ) );
}

function zaito_direct_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $tab  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
    $c    = zaito_direct_admin_counts();
    $tabs = array(
        'overview'  => '概要',
        'companies' => '企業' . ( $c['companies'] ? '（承認待ち ' . $c['companies'] . '）' : '' ),
        'jobs'      => '求人の審査' . ( $c['jobs'] ? '（' . $c['jobs'] . '）' : '' ),
        'apps'      => '応募・やりとり',
        'offers'    => '内定・成果報酬' . ( $c['offers'] ? '（未請求 ' . $c['offers'] . '）' : '' ),
        'settings'  => '設定',
    );
    if ( ! isset( $tabs[ $tab ] ) ) {
        $tab = 'overview';
    }
    echo '<div class="wrap"><h1>直接掲載</h1>';
    if ( isset( $_GET['done'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>保存しました。</p></div>';
    }
    echo '<nav class="nav-tab-wrapper">';
    foreach ( $tabs as $k => $label ) {
        printf( '<a href="%s" class="nav-tab%s">%s</a>', esc_url( zaito_direct_admin_url( $k ) ), $k === $tab ? ' nav-tab-active' : '', esc_html( $label ) );
    }
    echo '</nav><div style="margin-top:16px">';
    call_user_func( 'zaito_direct_admin_tab_' . $tab );
    echo '</div></div>';
}

function zaito_direct_admin_tab_overview() {
    $c     = zaito_direct_admin_counts();
    $apps  = zaito_direct_applications( 'all' );
    $count = array_fill_keys( array_keys( zaito_direct_app_statuses() ), 0 );
    foreach ( $apps as $a ) {
        $count[ zaito_direct_app_status( $a->ID ) ]++;
    }
    $open = count( zaito_direct_jobs() );
    $rows = array(
        array( '承認待ちの企業', $c['companies'], 'companies' ),
        array( '審査待ちの求人', $c['jobs'], 'jobs' ),
        array( '掲載中の求人', $open, 'jobs' ),
        array( '応募（すべて）', count( $apps ), 'apps' ),
        array( '3日以上返信がない応募', $c['stale'], 'apps' ),
        array( '内定（すべて）', $count['offer'], 'offers' ),
        array( '未請求の成果報酬', $c['offers'], 'offers' ),
    );
    echo '<table class="widefat striped" style="max-width:520px"><tbody>';
    foreach ( $rows as $r ) {
        printf( '<tr><th><a href="%s">%s</a></th><td style="text-align:right;font-size:16px"><b>%d</b></td></tr>', esc_url( zaito_direct_admin_url( $r[2] ) ), esc_html( $r[0] ), (int) $r[1] );
    }
    echo '</tbody></table>';
    echo '<p>企業の登録ページ: <a href="' . esc_url( home_url( '/company-register/' ) ) . '" target="_blank">' . esc_html( home_url( '/company-register/' ) ) . '</a></p>';
}

function zaito_direct_admin_tab_companies() {
    $users  = get_users( array( 'role' => 'zaito_company', 'orderby' => 'registered', 'order' => 'DESC' ) );
    $labels = array( 'pending' => '承認待ち', 'approved' => '承認済み', 'suspended' => '停止中' );
    echo '<table class="widefat striped"><thead><tr><th>会社</th><th>担当・連絡先</th><th>登録日</th><th>メール確認</th><th>求人</th><th>状態</th><th>操作</th></tr></thead><tbody>';
    if ( ! $users ) {
        echo '<tr><td colspan="7">まだ企業の登録はありません。</td></tr>';
    }
    foreach ( $users as $u ) {
        $st   = zaito_company_status( $u->ID );
        $url  = (string) get_user_meta( $u->ID, 'company_url', true );
        $jobs = count( get_posts( array( 'post_type' => 'job_listing', 'post_status' => 'any', 'posts_per_page' => 100, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_company_user_id', 'value' => $u->ID ), array( 'key' => '_zaito_direct', 'value' => '1' ) ) ) ) );
        echo '<tr>';
        echo '<td><b>' . esc_html( zaito_company_name( $u->ID ) ) . '</b>' . ( $url ? '<br><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( $url ) . '</a>' : '' ) . '</td>';
        echo '<td>' . esc_html( $u->first_name ) . '<br>' . esc_html( $u->user_email ) . '<br>' . esc_html( get_user_meta( $u->ID, 'company_phone', true ) ) . '</td>';
        echo '<td>' . esc_html( mysql2date( 'Y/n/j', $u->user_registered ) ) . '</td>';
        echo '<td>' . ( zaito_is_email_verified( $u->ID ) ? '済み' : 'まだ' ) . '</td>';
        echo '<td>' . (int) $jobs . '</td>';
        echo '<td><b>' . esc_html( $labels[ $st ] ) . '</b></td><td>';
        foreach ( array( 'approved' => '承認する', 'suspended' => '停止する', 'pending' => '承認待ちに戻す' ) as $to => $label ) {
            if ( $to === $st ) {
                continue;
            }
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:0 4px 4px 0">';
            wp_nonce_field( 'zaito_direct_admin_company_' . $u->ID );
            echo '<input type="hidden" name="action" value="zaito_direct_admin_company"><input type="hidden" name="user_id" value="' . (int) $u->ID . '"><input type="hidden" name="to" value="' . esc_attr( $to ) . '">';
            echo '<button class="button' . ( 'approved' === $to ? ' button-primary' : '' ) . '"' . ( 'suspended' === $to ? ' onclick="return confirm(\'この企業を停止しますか？求人は一覧から消え、メッセージも送れなくなります。\')"' : '' ) . '>' . esc_html( $label ) . '</button></form>';
        }
        echo '</td></tr>';
    }
    echo '</tbody></table>';
}

function zaito_handle_direct_admin_company() {
    $uid = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
    check_admin_referer( 'zaito_direct_admin_company_' . $uid );
    $to = isset( $_POST['to'] ) ? sanitize_key( $_POST['to'] ) : '';
    if ( ! current_user_can( 'manage_options' ) || ! in_array( $to, array( 'pending', 'approved', 'suspended' ), true ) ) {
        wp_die( '権限がありません' );
    }
    $user = get_userdata( $uid );
    if ( $user && zaito_is_company( $user ) ) {
        $was = zaito_company_status( $uid );
        update_user_meta( $uid, 'zaito_company_status', $to );
        if ( 'approved' === $to && 'approved' !== $was ) {
            zaito_mail_button(
                $user->user_email,
                '【zaito】企業アカウントの審査が完了しました',
                '審査が完了しました',
                zaito_company_name( $uid ) . ' 様' . "\n\n" . 'zaitoの企業アカウントの審査が完了しました。求人を作成して審査に出すと、運営が出社の有無などを確認したうえで公開します。',
                '求人を作成する',
                home_url( '/company-jobs/?new=1' ),
                '',
                '学生からの応募が「内定」になった時点で、成果報酬が発生します。',
                zaito_direct_from()
            );
        }
    }
    wp_safe_redirect( zaito_direct_admin_url( 'companies', array( 'done' => 1 ) ) );
    exit;
}
add_action( 'admin_post_zaito_direct_admin_company', 'zaito_handle_direct_admin_company' );

function zaito_direct_admin_tab_jobs() {
    $jobs = get_posts( array(
        'post_type'      => 'job_listing',
        'post_status'    => 'any',
        'posts_per_page' => 200,
        'orderby'        => 'modified',
        'order'          => 'DESC',
        'meta_query'     => array( array( 'key' => '_zaito_direct', 'value' => '1' ) ),
    ) );
    // 審査待ちを先に。
    usort( $jobs, function ( $a, $b ) {
        return ( 'pending' === zaito_direct_review( $b->ID ) ) - ( 'pending' === zaito_direct_review( $a->ID ) );
    } );
    $fields = array(
        '_job_category' => '職種', '_job_employment_type' => '契約の形', '_job_hours' => '稼働条件', '_job_target_text' => '応募できる人',
        '_job_screening_question' => '応募者への質問',
    );
    if ( ! $jobs ) {
        echo '<p>まだ求人はありません。</p>';
    }
    foreach ( $jobs as $p ) {
        $owner  = (int) get_post_meta( $p->ID, '_company_user_id', true );
        $state  = zaito_direct_job_state( $p );
        $review = zaito_direct_review( $p->ID );
        echo '<div class="postbox" style="padding:12px 16px;max-width:900px' . ( 'pending' === $review ? ';border-left:4px solid #d63638' : '' ) . '">';
        echo '<p style="margin:0 0 4px;color:#646970">' . esc_html( zaito_company_name( $owner ) ) . '（企業: ' . esc_html( array( 'pending' => '承認待ち', 'approved' => '承認済み', 'suspended' => '停止中' )[ zaito_company_status( $owner ) ] ) . '）・状態: <b>' . esc_html( $state[1] ) . '</b></p>';
        echo '<h2 style="margin:0 0 8px">' . esc_html( $p->post_title ) . '</h2>';
        echo '<p><b>' . esc_html( zaito_direct_pay_text( $p->ID ) ) . '</b>　' . esc_html( get_post_meta( $p->ID, '_job_summary', true ) ) . '</p>';
        // 審査待ち以外は、中身をたたんでおく。
        echo 'pending' === $review ? '<div>' : '<details><summary style="cursor:pointer;margin-bottom:8px">求人の内容を見る</summary>';
        echo '<table class="widefat" style="margin-bottom:8px"><tbody>';
        foreach ( $fields as $k => $label ) {
            echo '<tr><th style="width:140px">' . esc_html( $label ) . '</th><td>' . esc_html( get_post_meta( $p->ID, $k, true ) ) . '</td></tr>';
        }
        echo '<tr><th>出社なしの確認</th><td>' . ( get_post_meta( $p->ID, '_job_confirm_remote', true ) ? '企業が確認済み' : '<b style="color:#d63638">未確認</b>' ) . '</td></tr>';
        echo '<tr><th>仕事内容</th><td>' . nl2br( esc_html( $p->post_content ) ) . '</td></tr>';
        echo '</tbody></table>';
        echo 'pending' === $review ? '</div>' : '</details>';
        if ( 'returned' === $review ) {
            echo '<p>差し戻しの理由: ' . esc_html( get_post_meta( $p->ID, '_zaito_review_note', true ) ) . '</p>';
        }
        if ( 'publish' === $p->post_status && ! get_post_meta( $p->ID, '_zaito_closed', true ) ) {
            echo '<p><a href="' . esc_url( home_url( '/remote/' . zaito_direct_slug( $p->ID ) . '/' ) ) . '" target="_blank">公開ページを見る</a></p>';
        }
        if ( 'pending' === $review || 'publish' === $p->post_status ) {
            echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
            wp_nonce_field( 'zaito_direct_admin_job_' . $p->ID );
            echo '<input type="hidden" name="action" value="zaito_direct_admin_job"><input type="hidden" name="job_id" value="' . (int) $p->ID . '">';
            if ( 'pending' === $review ) {
                echo '<button class="button button-primary" name="to" value="approve">承認して公開する</button> ';
            }
            echo '<input type="text" name="note" placeholder="差し戻し・非公開の理由（企業に送ります）" style="width:340px"> <button class="button" name="to" value="return">' . ( 'publish' === $p->post_status ? '非公開にして差し戻す' : '差し戻す' ) . '</button>';
            echo '</form>';
        }
        echo '</div>';
    }
}

function zaito_handle_direct_admin_job() {
    $job_id = isset( $_POST['job_id'] ) ? (int) $_POST['job_id'] : 0;
    check_admin_referer( 'zaito_direct_admin_job_' . $job_id );
    if ( ! current_user_can( 'manage_options' ) || ! get_post_meta( $job_id, '_zaito_direct', true ) ) {
        wp_die( '権限がありません' );
    }
    $to    = isset( $_POST['to'] ) ? sanitize_key( $_POST['to'] ) : '';
    $note  = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : '';
    $owner = get_userdata( (int) get_post_meta( $job_id, '_company_user_id', true ) );
    $title = get_the_title( $job_id );
    if ( 'approve' === $to ) {
        wp_update_post( array( 'ID' => $job_id, 'post_status' => 'publish' ) );
        update_post_meta( $job_id, '_zaito_review', 'approved' );
        update_post_meta( $job_id, '_zaito_approved_at', current_time( 'mysql' ) );
        if ( $owner ) {
            $live = 'approved' === zaito_company_status( $owner->ID );
            zaito_mail_button( $owner->user_email, '【zaito】求人を公開しました', '求人を公開しました', '「' . $title . '」の審査が終わり、' . ( $live ? 'zaitoに公開しました。' : '求人の確認が終わりました。企業アカウントの審査が終わると公開されます。' ), '公開中の求人を見る', home_url( '/company-jobs/' ), '', '応募があると、メールでお知らせします。', zaito_direct_from() );
        }
    } elseif ( 'return' === $to ) {
        wp_update_post( array( 'ID' => $job_id, 'post_status' => 'draft' ) );
        update_post_meta( $job_id, '_zaito_review', 'returned' );
        update_post_meta( $job_id, '_zaito_review_note', $note );
        if ( $owner ) {
            zaito_mail_button( $owner->user_email, '【zaito】求人の内容をご確認ください', '求人の内容をご確認ください', '「' . $title . '」は、次の理由でまだ公開できません。内容を直して、もう一度審査に出してください。' . "\n\n" . ( $note ? $note : '（理由はメッセージでお送りします）' ), '求人を直す', home_url( '/company-jobs/?edit=' . $job_id ), '', 'zaitoは、面接・初日・研修・会議も含めて出社のない求人だけを掲載しています。', zaito_direct_from() );
        }
    }
    wp_safe_redirect( zaito_direct_admin_url( 'jobs', array( 'done' => 1 ) ) );
    exit;
}
add_action( 'admin_post_zaito_direct_admin_job', 'zaito_handle_direct_admin_job' );

function zaito_direct_admin_tab_apps() {
    $apps   = zaito_direct_applications( 'all' );
    $labels = zaito_direct_app_statuses();
    echo '<p>やりとりの中身は「開く」から読めます（運営は読むだけで、送ることはできません）。赤い行は、応募から3日以上たっても企業が返信していない応募です。</p>';
    echo '<table class="widefat striped"><thead><tr><th>応募日</th><th>応募者</th><th>企業・求人</th><th>状態</th><th>最初の返信</th><th>最後のメッセージ</th><th></th></tr></thead><tbody>';
    if ( ! $apps ) {
        echo '<tr><td colspan="7">まだ応募はありません。</td></tr>';
    }
    foreach ( $apps as $a ) {
        $job   = get_post( (int) get_post_meta( $a->ID, 'job_id', true ) );
        $stale = zaito_direct_is_stale( $a );
        $first = (string) get_post_meta( $a->ID, 'first_reply_at', true );
        $last  = (string) get_post_meta( $a->ID, 'last_message_at', true );
        echo '<tr' . ( $stale ? ' style="background:#fcf0f1"' : '' ) . '>';
        echo '<td>' . esc_html( get_the_date( 'Y/n/j H:i', $a ) ) . '</td>';
        echo '<td>' . esc_html( get_post_meta( $a->ID, 'applicant_name', true ) ) . '<br><small>' . esc_html( zaito_direct_applicant_line( $a->ID ) ) . '</small></td>';
        echo '<td>' . esc_html( zaito_company_name( (int) get_post_meta( $a->ID, 'company_id', true ) ) ) . '<br><small>' . esc_html( $job ? $job->post_title : '' ) . '</small></td>';
        echo '<td>' . esc_html( $labels[ zaito_direct_app_status( $a->ID ) ] ) . '</td>';
        echo '<td>' . ( $first ? esc_html( mysql2date( 'n/j H:i', $first ) ) : '<b>' . ( $stale ? '返信なし' : 'まだ' ) . '</b>' ) . '</td>';
        echo '<td>' . ( $last ? esc_html( mysql2date( 'n/j H:i', $last ) ) . '（' . ( 'company' === get_post_meta( $a->ID, 'last_sender', true ) ? '企業' : '応募者' ) . '）' : '' ) . '</td>';
        echo '<td><a href="' . esc_url( zaito_direct_chat_url( $a->ID ) ) . '" target="_blank">開く</a></td></tr>';
    }
    echo '</tbody></table>';
}

function zaito_direct_admin_tab_offers() {
    $apps = array_filter( zaito_direct_applications( 'all' ), function ( $a ) {
        return (bool) get_post_meta( $a->ID, 'offer_at', true );
    } );
    $fee_labels = zaito_direct_fee_statuses();
    $default    = (string) get_option( 'zaito_direct_fee_default', '' );
    echo '<p>企業が応募を「内定」にした時点で、ここに記録されます。金額の初期値は「設定」タブで決められます' . ( '' === $default ? '（<b>まだ決まっていません</b>）' : '（今は ' . esc_html( $default ) . '）' ) . '。</p>';
    echo '<table class="widefat striped"><thead><tr><th>内定日</th><th>企業・求人</th><th>応募者</th><th>今の状態</th><th>金額・請求</th></tr></thead><tbody>';
    if ( ! $apps ) {
        echo '<tr><td colspan="5">まだ内定はありません。</td></tr>';
    }
    foreach ( $apps as $a ) {
        $job = get_post( (int) get_post_meta( $a->ID, 'job_id', true ) );
        $fs  = (string) get_post_meta( $a->ID, 'fee_status', true );
        echo '<tr><td>' . esc_html( mysql2date( 'Y/n/j', get_post_meta( $a->ID, 'offer_at', true ) ) ) . '</td>';
        echo '<td>' . esc_html( zaito_company_name( (int) get_post_meta( $a->ID, 'company_id', true ) ) ) . '<br><small>' . esc_html( $job ? $job->post_title : '' ) . '</small></td>';
        echo '<td>' . esc_html( get_post_meta( $a->ID, 'applicant_name', true ) ) . '</td>';
        echo '<td>' . esc_html( zaito_direct_app_statuses()[ zaito_direct_app_status( $a->ID ) ] ) . ( 'offer' !== zaito_direct_app_status( $a->ID ) ? '<br><small>内定のあとに状態が変わっています</small>' : '' ) . '</td><td>';
        echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        wp_nonce_field( 'zaito_direct_admin_fee_' . $a->ID );
        echo '<input type="hidden" name="action" value="zaito_direct_admin_fee"><input type="hidden" name="app_id" value="' . (int) $a->ID . '">';
        echo '<input type="text" name="amount" value="' . esc_attr( get_post_meta( $a->ID, 'fee_amount', true ) ) . '" placeholder="金額（例: 30,000円）" style="width:150px"> ';
        echo '<select name="fee_status">';
        foreach ( $fee_labels as $k => $l ) {
            echo '<option value="' . esc_attr( $k ) . '"' . selected( $fs, $k, false ) . '>' . esc_html( $l ) . '</option>';
        }
        echo '</select> <input type="text" name="fee_note" value="' . esc_attr( get_post_meta( $a->ID, 'fee_note', true ) ) . '" placeholder="メモ" style="width:160px"> <button class="button">保存</button></form></td></tr>';
    }
    echo '</tbody></table>';
}

function zaito_handle_direct_admin_fee() {
    $app_id = isset( $_POST['app_id'] ) ? (int) $_POST['app_id'] : 0;
    check_admin_referer( 'zaito_direct_admin_fee_' . $app_id );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( '権限がありません' );
    }
    $fs = isset( $_POST['fee_status'] ) ? sanitize_key( $_POST['fee_status'] ) : '';
    if ( array_key_exists( $fs, zaito_direct_fee_statuses() ) ) {
        update_post_meta( $app_id, 'fee_status', $fs );
    }
    update_post_meta( $app_id, 'fee_amount', isset( $_POST['amount'] ) ? sanitize_text_field( wp_unslash( $_POST['amount'] ) ) : '' );
    update_post_meta( $app_id, 'fee_note', isset( $_POST['fee_note'] ) ? sanitize_text_field( wp_unslash( $_POST['fee_note'] ) ) : '' );
    wp_safe_redirect( zaito_direct_admin_url( 'offers', array( 'done' => 1 ) ) );
    exit;
}
add_action( 'admin_post_zaito_direct_admin_fee', 'zaito_handle_direct_admin_fee' );

function zaito_direct_admin_tab_settings() {
    echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
    wp_nonce_field( 'zaito_direct_admin_settings' );
    echo '<input type="hidden" name="action" value="zaito_direct_admin_settings"><table class="form-table"><tbody>';
    echo '<tr><th>成果報酬の金額（初期値）</th><td><input type="text" name="fee_default" value="' . esc_attr( get_option( 'zaito_direct_fee_default', '' ) ) . '" placeholder="例: 30,000円" class="regular-text"><p class="description">内定が出たときの記録に、この金額が入ります（記録ごとにあとから変えられます）。空欄なら「未定」のまま記録します。企業の画面には表示しません。</p></td></tr>';
    echo '<tr><th>運営へのお知らせ先</th><td><input type="email" name="ops_email" value="' . esc_attr( zaito_direct_ops_email() ) . '" class="regular-text"><p class="description">企業の登録・求人の審査依頼・応募・内定をこのアドレスに知らせます。</p></td></tr>';
    echo '</tbody></table><p><button class="button button-primary">保存</button></p></form>';
}

function zaito_handle_direct_admin_settings() {
    check_admin_referer( 'zaito_direct_admin_settings' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( '権限がありません' );
    }
    update_option( 'zaito_direct_fee_default', isset( $_POST['fee_default'] ) ? sanitize_text_field( wp_unslash( $_POST['fee_default'] ) ) : '', false );
    $mail = isset( $_POST['ops_email'] ) ? sanitize_email( wp_unslash( $_POST['ops_email'] ) ) : '';
    if ( $mail && is_email( $mail ) ) {
        update_option( 'zaito_direct_ops_email', $mail, false );
    }
    wp_safe_redirect( zaito_direct_admin_url( 'settings', array( 'done' => 1 ) ) );
    exit;
}
add_action( 'admin_post_zaito_direct_admin_settings', 'zaito_handle_direct_admin_settings' );
