<?php
/**
 * 正式ローンチ前のLP用フォーム受付（学生Waiting List・企業問い合わせ）。
 *
 * - 学生Waiting List: 既存の「興味あり登録」(zaito_interest) にメールアドレスだけで
 *   登録する。/interest/ ページからの登録と同じ投稿タイプにまとめ、ローンチ時に
 *   そのまま本登録の案内に使えるようにする。
 * - 企業問い合わせ: 「企業問い合わせ」(zaito_company_lead) に保存し、管理者へ
 *   メール通知する。
 *
 * LPはJSのfetchで送信し、JSONで結果を受け取る（ページ遷移なしで完了表示）。
 * 匿名の公開登録フォームのためnonceは使わない（LPがキャッシュされた場合に
 * 古いnonceで全員が送信失敗になるのを避けるため）。代わりにハニーポット・
 * IP単位の回数制限・メールアドレスの重複チェックで守る。
 */

function zaito_register_lead_post_types() {
    register_post_type( 'zaito_company_lead', array(
        'label'           => '企業問い合わせ',
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_icon'       => 'dashicons-building',
        'supports'        => array( 'title', 'custom-fields' ),
        'capability_type' => 'post',
    ) );
}
add_action( 'init', 'zaito_register_lead_post_types' );

/**
 * 流入経路（UTMパラメータ・参照元）をPOSTから取り出す。
 * CPAを経路ごとに計測できるよう、登録データと一緒に保存する。
 */
function zaito_lp_tracking_fields() {
    $fields = array();
    foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign', 'referrer' ) as $key ) {
        $fields[ $key ] = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
    }
    return $fields;
}

/**
 * 同じIPから短時間に大量送信されるのを防ぐ（10分間に5回まで）。
 */
function zaito_lp_rate_limited( $bucket ) {
    $ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $key = 'zaito_lp_rl_' . md5( $bucket . '|' . $ip );
    $count = (int) get_transient( $key );
    if ( $count >= 5 ) {
        return true;
    }
    set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
    return false;
}

/**
 * LPのJS（Accept: application/json）にはJSONで、JS無効環境の通常送信には
 * トップページへのリダイレクトで応答する。
 */
function zaito_lp_respond( $success, $data, $redirect_flag ) {
    $accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? $_SERVER['HTTP_ACCEPT'] : '';
    if ( false !== strpos( $accept, 'application/json' ) ) {
        if ( $success ) {
            wp_send_json_success( $data );
        } else {
            wp_send_json_error( $data, 400 );
        }
        return;
    }
    wp_safe_redirect( add_query_arg( $redirect_flag, $success ? 'done' : 'error', home_url( '/' ) ) );
    exit;
}

function zaito_handle_lp_waitlist() {
    // ハニーポット: ボットには成功したように見せて何も保存しない。
    if ( ! empty( $_POST['website'] ) ) {
        zaito_lp_respond( true, array( 'already' => false ), 'waitlist' );
        return;
    }

    $email = isset( $_POST['email'] ) ? strtolower( trim( sanitize_email( wp_unslash( $_POST['email'] ) ) ) ) : '';
    if ( ! $email || ! is_email( $email ) ) {
        zaito_lp_respond( false, array( 'message' => '正しいメールアドレスを入力してください' ), 'waitlist' );
        return;
    }

    if ( zaito_lp_rate_limited( 'waitlist' ) ) {
        zaito_lp_respond( false, array( 'message' => '送信回数が多すぎます。しばらくしてからお試しください' ), 'waitlist' );
        return;
    }

    $existing = get_posts( array(
        'post_type'      => 'zaito_interest',
        'post_status'    => 'any',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array( 'key' => 'email', 'value' => $email ),
        ),
    ) );
    if ( ! empty( $existing ) ) {
        zaito_lp_respond( true, array( 'already' => true ), 'waitlist' );
        return;
    }

    $interest_id = wp_insert_post( array(
        'post_type'   => 'zaito_interest',
        'post_title'  => $email,
        'post_status' => 'private',
    ) );

    if ( ! $interest_id || is_wp_error( $interest_id ) ) {
        zaito_lp_respond( false, array( 'message' => '登録に失敗しました。時間をおいて再度お試しください' ), 'waitlist' );
        return;
    }

    update_post_meta( $interest_id, 'email', $email );
    update_post_meta( $interest_id, 'source', 'lp' );
    foreach ( zaito_lp_tracking_fields() as $key => $value ) {
        update_post_meta( $interest_id, $key, $value );
    }

    zaito_lp_respond( true, array( 'already' => false ), 'waitlist' );
}
add_action( 'wp_ajax_zaito_lp_waitlist', 'zaito_handle_lp_waitlist' );
add_action( 'wp_ajax_nopriv_zaito_lp_waitlist', 'zaito_handle_lp_waitlist' );

function zaito_handle_lp_company_inquiry() {
    if ( ! empty( $_POST['website'] ) ) {
        zaito_lp_respond( true, array(), 'inquiry' );
        return;
    }

    $company = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
    $name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    $email   = isset( $_POST['email'] ) ? strtolower( trim( sanitize_email( wp_unslash( $_POST['email'] ) ) ) ) : '';
    $phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

    if ( ! $email || ! is_email( $email ) ) {
        zaito_lp_respond( false, array( 'message' => '正しいメールアドレスを入力してください' ), 'inquiry' );
        return;
    }

    if ( zaito_lp_rate_limited( 'inquiry' ) ) {
        zaito_lp_respond( false, array( 'message' => '送信回数が多すぎます。しばらくしてからお試しください' ), 'inquiry' );
        return;
    }

    $lead_id = wp_insert_post( array(
        'post_type'   => 'zaito_company_lead',
        'post_title'  => ( $company ? $company : '(会社名未入力)' ) . ' (' . $email . ')',
        'post_status' => 'private',
    ) );

    if ( ! $lead_id || is_wp_error( $lead_id ) ) {
        zaito_lp_respond( false, array( 'message' => '送信に失敗しました。時間をおいて再度お試しください' ), 'inquiry' );
        return;
    }

    $tracking = zaito_lp_tracking_fields();
    update_post_meta( $lead_id, 'company', $company );
    update_post_meta( $lead_id, 'name', $name );
    update_post_meta( $lead_id, 'email', $email );
    update_post_meta( $lead_id, 'phone', $phone );
    update_post_meta( $lead_id, 'message', $message );
    foreach ( $tracking as $key => $value ) {
        update_post_meta( $lead_id, $key, $value );
    }

    // 企業からの問い合わせは営業上すぐ対応したいので、管理者にメールで通知する。
    $body = "LPの企業向けフォームから問い合わせがありました。\n\n"
        . '会社名: ' . $company . "\n"
        . 'ご担当者: ' . $name . "\n"
        . 'メール: ' . $email . "\n"
        . '電話: ' . $phone . "\n"
        . "内容:\n" . $message . "\n\n"
        . '流入元: ' . $tracking['utm_source'] . ' / ' . $tracking['utm_medium'] . ' / ' . $tracking['utm_campaign'] . "\n"
        . '管理画面: ' . admin_url( 'edit.php?post_type=zaito_company_lead' ) . "\n";
    wp_mail( get_option( 'admin_email' ), '【zaito】企業から問い合わせがありました（' . ( $company ? $company : $email ) . '）', $body );

    zaito_lp_respond( true, array(), 'inquiry' );
}
add_action( 'wp_ajax_zaito_lp_company_inquiry', 'zaito_handle_lp_company_inquiry' );
add_action( 'wp_ajax_nopriv_zaito_lp_company_inquiry', 'zaito_handle_lp_company_inquiry' );

/**
 * CSV出力の列定義。キーはpost meta名（'_date'は登録日時）。
 */
function zaito_lead_export_columns( $post_type ) {
    if ( 'zaito_company_lead' === $post_type ) {
        return array(
            '_date'        => '受付日時',
            'company'      => '会社名',
            'name'         => 'ご担当者',
            'email'        => 'メールアドレス',
            'phone'        => '電話番号',
            'message'      => '内容',
            'utm_source'   => 'utm_source',
            'utm_medium'   => 'utm_medium',
            'utm_campaign' => 'utm_campaign',
            'referrer'     => '参照元URL',
        );
    }
    return array(
        '_date'        => '登録日時',
        'email'        => 'メールアドレス',
        'name'         => 'お名前',
        'interests'    => '興味のある案件',
        'hours'        => '稼働可能時間',
        'source'       => '登録元',
        'utm_source'   => 'utm_source',
        'utm_medium'   => 'utm_medium',
        'utm_campaign' => 'utm_campaign',
        'referrer'     => '参照元URL',
    );
}

/**
 * Excelで開いたときに数式として実行されないよう、先頭が = + - @ のセルを無害化する。
 */
function zaito_csv_safe_cell( $value ) {
    if ( is_array( $value ) ) {
        $value = implode( ' / ', $value );
    }
    $value = (string) $value;
    if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@' ), true ) ) {
        $value = "'" . $value;
    }
    return $value;
}

function zaito_handle_export_leads() {
    $post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : '';
    if ( ! in_array( $post_type, array( 'zaito_interest', 'zaito_company_lead' ), true ) ) {
        wp_die( '不正なリクエストです。' );
    }
    check_admin_referer( 'zaito_export_' . $post_type );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'この操作を行う権限がありません。' );
    }

    $columns = zaito_lead_export_columns( $post_type );
    $posts = get_posts( array(
        'post_type'      => $post_type,
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
    ) );

    nocache_headers();
    header( 'Content-Type: text/csv; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename="' . $post_type . '-' . gmdate( 'Ymd' ) . '.csv"' );

    $out = fopen( 'php://output', 'w' );
    fwrite( $out, "\xEF\xBB\xBF" ); // Excelで文字化けしないようにBOMを付ける
    fputcsv( $out, array_values( $columns ) );
    foreach ( $posts as $post ) {
        $row = array();
        foreach ( array_keys( $columns ) as $key ) {
            if ( '_date' === $key ) {
                $row[] = get_the_date( 'Y-m-d H:i', $post );
                continue;
            }
            $value = get_post_meta( $post->ID, $key, true );
            if ( 'source' === $key && '' === $value ) {
                $value = 'interest_page';
            }
            $row[] = zaito_csv_safe_cell( $value );
        }
        fputcsv( $out, $row );
    }
    fclose( $out );
    exit;
}
add_action( 'admin_post_zaito_export_leads', 'zaito_handle_export_leads' );

/**
 * 管理画面の一覧上部に「CSVダウンロード」リンクを出す。
 */
function zaito_lead_export_link( $views ) {
    $screen = get_current_screen();
    if ( ! $screen || ! current_user_can( 'manage_options' ) ) {
        return $views;
    }
    $url = wp_nonce_url(
        admin_url( 'admin-post.php?action=zaito_export_leads&post_type=' . $screen->post_type ),
        'zaito_export_' . $screen->post_type
    );
    $views['zaito_export'] = '<a href="' . esc_url( $url ) . '">CSVダウンロード</a>';
    return $views;
}
add_filter( 'views_edit-zaito_interest', 'zaito_lead_export_link' );
add_filter( 'views_edit-zaito_company_lead', 'zaito_lead_export_link' );

/**
 * 管理画面の一覧にメールアドレス・流入元の列を足し、一覧だけで状況が分かるようにする。
 */
function zaito_lead_admin_columns( $columns ) {
    $date = $columns['date'];
    unset( $columns['date'] );
    $columns['zaito_email']  = 'メールアドレス';
    $columns['zaito_source'] = '流入元';
    $columns['date']         = $date;
    return $columns;
}
add_filter( 'manage_zaito_interest_posts_columns', 'zaito_lead_admin_columns' );
add_filter( 'manage_zaito_company_lead_posts_columns', 'zaito_lead_admin_columns' );

function zaito_lead_admin_column_value( $column, $post_id ) {
    if ( 'zaito_email' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'email', true ) );
    } elseif ( 'zaito_source' === $column ) {
        $source = get_post_meta( $post_id, 'utm_source', true );
        echo esc_html( $source ? $source : '—' );
    }
}
add_action( 'manage_zaito_interest_posts_custom_column', 'zaito_lead_admin_column_value', 10, 2 );
add_action( 'manage_zaito_company_lead_posts_custom_column', 'zaito_lead_admin_column_value', 10, 2 );
