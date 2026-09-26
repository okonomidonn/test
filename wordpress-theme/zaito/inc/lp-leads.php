<?php
/**
 * 正式ローンチ前のLP用フォーム受付（学生Waiting List・企業問い合わせ）。
 *
 * - 学生Waiting List: 既存の「興味あり登録」(zaito_interest) にメールアドレスだけで
 *   登録する（STEP1）。/interest/ ページからの登録と同じ投稿タイプにまとめ、ローンチ時に
 *   そのまま本登録の案内に使えるようにする。登録後に任意で「興味のある仕事」を
 *   同じレコードに追記できる（STEP2）。STEP1の応答で返すトークンが必要。
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
        zaito_lp_respond( true, array( 'already' => true, 'token' => zaito_lp_issue_token( $existing[0] ) ), 'waitlist' );
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

    // 登録済みのアドレスには送らない（第三者が同じアドレスで何度も送らせることを防ぐ）。
    zaito_lp_send_waitlist_thanks( $email );

    zaito_lp_respond( true, array( 'already' => false, 'token' => zaito_lp_issue_token( $interest_id ) ), 'waitlist' );
}

/**
 * 先行登録した学生への自動返信メール。
 */
function zaito_lp_send_waitlist_thanks( $email ) {
    $host = preg_replace( '/^www\./', '', (string) wp_parse_url( home_url(), PHP_URL_HOST ) );

    $subject = '【zaito】先行登録ありがとうございます';
    $body    = "zaitoへの先行登録ありがとうございます。\n"
        . "以下のメールアドレスで登録を受け付けました。\n\n"
        . '登録メールアドレス: ' . $email . "\n\n"
        . "zaitoは、大学生・若手向けの完全在宅求人サービスです。\n"
        . "現在、正式ローンチに向けて掲載企業・求人を準備しています。\n"
        . "公開の準備ができましたら、このメールアドレスにいち早くお知らせします。\n\n"
        . "公開までもうしばらくお待ちください。\n\n"
        . "※このメールは送信専用のアドレスから自動でお送りしています。返信いただいてもお答えできません。\n"
        . "※お心当たりのない場合は、お手数ですがこのメールを破棄してください。\n\n"
        . "──────────\n"
        . "zaito（ザイト）\n"
        . home_url( '/' ) . "\n";

    wp_mail( $email, $subject, $body, array( 'From: zaito <noreply@' . $host . '>' ) );
}
add_action( 'wp_ajax_zaito_lp_waitlist', 'zaito_handle_lp_waitlist' );
add_action( 'wp_ajax_nopriv_zaito_lp_waitlist', 'zaito_handle_lp_waitlist' );

/**
 * STEP2（興味のある仕事）の書き込みを、直前にSTEP1を完了したブラウザに限るためのトークン。
 * メールアドレスを知っているだけの第三者が、他人の登録内容を書き換えられないようにする。
 */
function zaito_lp_issue_token( $interest_id ) {
    $token = wp_generate_password( 32, false );
    update_post_meta( $interest_id, 'lp_token', $token );
    return $token;
}

function zaito_handle_lp_waitlist_jobs() {
    $email = isset( $_POST['email'] ) ? strtolower( trim( sanitize_email( wp_unslash( $_POST['email'] ) ) ) ) : '';
    $token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
    $jobs  = isset( $_POST['desired_jobs'] ) && is_array( $_POST['desired_jobs'] )
        ? array_values( array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['desired_jobs'] ) ) ) )
        : array();

    if ( ! $email || ! $token || empty( $jobs ) ) {
        zaito_lp_respond( false, array( 'message' => '入力内容を確認してください' ), 'waitlist' );
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
    if ( empty( $existing ) || ! hash_equals( (string) get_post_meta( $existing[0], 'lp_token', true ), $token ) ) {
        zaito_lp_respond( false, array( 'message' => '登録情報が確認できませんでした' ), 'waitlist' );
        return;
    }

    update_post_meta( $existing[0], 'interests', array_slice( $jobs, 0, 20 ) );
    zaito_lp_respond( true, array(), 'waitlist' );
}
add_action( 'wp_ajax_zaito_lp_waitlist_jobs', 'zaito_handle_lp_waitlist_jobs' );
add_action( 'wp_ajax_nopriv_zaito_lp_waitlist_jobs', 'zaito_handle_lp_waitlist_jobs' );

function zaito_handle_lp_company_inquiry() {
    if ( ! empty( $_POST['website'] ) ) {
        zaito_lp_respond( true, array(), 'inquiry' );
        return;
    }

    $fields = array();
    foreach ( zaito_company_lead_fields() as $key => $label ) {
        $raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
        $fields[ $key ] = in_array( $key, array( 'job', 'note' ), true ) ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
    }
    $fields['email'] = strtolower( trim( sanitize_email( $fields['email'] ) ) );
    $fields['url']   = esc_url_raw( $fields['url'] );
    $company = $fields['company'];
    $email   = $fields['email'];

    if ( ! $company || ! $fields['name'] || ! $fields['job'] ) {
        zaito_lp_respond( false, array( 'message' => '必須項目を入力してください' ), 'inquiry' );
        return;
    }
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
    foreach ( $fields + $tracking as $key => $value ) {
        update_post_meta( $lead_id, $key, $value );
    }

    // 企業からの問い合わせは営業上すぐ対応したいので、管理者にメールで通知する。
    $body = "LPの企業向けフォームから問い合わせがありました。\n\n";
    foreach ( zaito_company_lead_fields() as $key => $label ) {
        $body .= $label . ': ' . $fields[ $key ] . "\n";
    }
    $body .= "\n"
        . '流入元: ' . $tracking['utm_source'] . ' / ' . $tracking['utm_medium'] . ' / ' . $tracking['utm_campaign'] . "\n"
        . '管理画面: ' . admin_url( 'edit.php?post_type=zaito_company_lead' ) . "\n";
    wp_mail( get_option( 'admin_email' ), '【zaito】企業から問い合わせがありました（' . ( $company ? $company : $email ) . '）', $body );

    zaito_lp_respond( true, array(), 'inquiry' );
}
add_action( 'wp_ajax_zaito_lp_company_inquiry', 'zaito_handle_lp_company_inquiry' );
add_action( 'wp_ajax_nopriv_zaito_lp_company_inquiry', 'zaito_handle_lp_company_inquiry' );

/**
 * LPの企業向けフォームの項目（キーはフォームのname属性・post meta名）。
 */
function zaito_company_lead_fields() {
    return array(
        'company'     => '会社名',
        'name'        => '担当者名',
        'email'       => 'メールアドレス',
        'url'         => '会社URL',
        'job'         => '募集したい仕事内容',
        'pay'         => '想定報酬',
        'conditions'  => '勤務条件',
        'hours'       => '勤務時間',
        'weeklyHours' => '週の想定稼働時間',
        'note'        => '自由記述',
    );
}

/**
 * CSV出力の列定義。キーはpost meta名（'_date'は登録日時）。
 */
function zaito_lead_export_columns( $post_type ) {
    if ( 'zaito_company_lead' === $post_type ) {
        return array( '_date' => '受付日時' ) + zaito_company_lead_fields() + array(
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
        'interests'    => '興味のある仕事',
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

function zaito_interest_admin_columns( $columns ) {
    $columns = zaito_lead_admin_columns( $columns );
    $date    = $columns['date'];
    unset( $columns['date'] );
    $columns['zaito_interests'] = '興味のある仕事';
    $columns['date']            = $date;
    return $columns;
}
add_filter( 'manage_zaito_interest_posts_columns', 'zaito_interest_admin_columns' );
add_filter( 'manage_zaito_company_lead_posts_columns', 'zaito_lead_admin_columns' );

function zaito_lead_admin_column_value( $column, $post_id ) {
    if ( 'zaito_email' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'email', true ) );
    } elseif ( 'zaito_source' === $column ) {
        $source = get_post_meta( $post_id, 'utm_source', true );
        echo esc_html( $source ? $source : '—' );
    } elseif ( 'zaito_interests' === $column ) {
        $interests = get_post_meta( $post_id, 'interests', true );
        echo esc_html( ! empty( $interests ) ? implode( '、', (array) $interests ) : '—' );
    }
}
add_action( 'manage_zaito_interest_posts_custom_column', 'zaito_lead_admin_column_value', 10, 2 );
add_action( 'manage_zaito_company_lead_posts_custom_column', 'zaito_lead_admin_column_value', 10, 2 );

/**
 * 1件を開いたときに、登録・問い合わせの内容を読みやすく表示する。
 * （標準の「カスタムフィールド」欄は初期状態で非表示のうえ、配列が読みにくいため）
 */
function zaito_lead_add_detail_box() {
    foreach ( array( 'zaito_interest', 'zaito_company_lead' ) as $post_type ) {
        add_meta_box( 'zaito_lead_detail', '登録内容', 'zaito_lead_render_detail_box', $post_type, 'normal', 'high' );
    }
}
add_action( 'add_meta_boxes', 'zaito_lead_add_detail_box' );

function zaito_lead_render_detail_box( $post ) {
    echo '<table class="widefat striped" style="border:0"><tbody>';
    foreach ( zaito_lead_export_columns( $post->post_type ) as $key => $label ) {
        $value = '_date' === $key ? get_the_date( 'Y-m-d H:i', $post ) : get_post_meta( $post->ID, $key, true );
        if ( is_array( $value ) ) {
            $value = implode( '、', $value );
        }
        if ( '' === (string) $value ) {
            $value = '—';
        }
        echo '<tr><th style="width:180px">' . esc_html( $label ) . '</th><td style="white-space:pre-wrap">' . esc_html( $value ) . '</td></tr>';
    }
    echo '</tbody></table>';
}
