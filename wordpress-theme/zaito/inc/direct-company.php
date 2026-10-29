<?php
/**
 * 直接掲載: 企業アカウントの登録・会社情報・求人の作成と編集（admin-post.php で受ける処理）。
 * 画面は page-company-*.php。共通の見た目は lp/biz-parts.php。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function zaito_direct_post_text( $key, $max = 200 ) {
    return isset( $_POST[ $key ] ) ? mb_substr( trim( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ), 0, $max ) : '';
}

function zaito_direct_post_textarea( $key, $max = 4000 ) {
    return isset( $_POST[ $key ] ) ? mb_substr( trim( sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) ), 0, $max ) : '';
}

/* ---------- 登録 ---------- */

function zaito_handle_register_company() {
    check_admin_referer( 'zaito_register_company' );
    $back = home_url( '/company-register/' );

    $email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $company  = zaito_direct_post_text( 'company_name', 80 );
    $person   = zaito_direct_post_text( 'contact_person', 40 );
    $phone    = zaito_direct_post_text( 'phone', 30 );
    $site     = isset( $_POST['company_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['company_url'] ) ) ) : '';
    $password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
    $agree    = isset( $_POST['agree_terms'] ) && '1' === $_POST['agree_terms'];

    $errors = array();
    if ( '' === $company ) {
        $errors[] = '会社名を入力してください';
    }
    if ( '' === $person ) {
        $errors[] = 'ご担当者名を入力してください';
    }
    if ( '' === $phone ) {
        $errors[] = '電話番号を入力してください';
    }
    if ( '' === $site ) {
        $errors[] = '会社のWebサイトのURLを入力してください（審査で使います）';
    }
    if ( ! $email || ! is_email( $email ) ) {
        $errors[] = 'メールアドレスを正しく入力してください';
    } elseif ( email_exists( $email ) ) {
        $errors[] = 'このメールアドレスはすでに登録されています。ログインしてください';
    }
    if ( strlen( $password ) < 8 ) {
        $errors[] = 'パスワードは8文字以上にしてください';
    }
    if ( ! $agree ) {
        $errors[] = '利用規約とプライバシーポリシーに同意してください';
    }
    if ( zaito_lp_rate_limited( 'company_register', 5 ) ) {
        $errors[] = '送信回数が多すぎます。しばらくしてからお試しください';
    }
    if ( $errors ) {
        zaito_store_form_errors_and_redirect( $errors, $back );
    }

    $user_id = wp_insert_user( array(
        'user_email' => $email,
        'user_login' => sanitize_user( $email ),
        'user_pass'  => $password,
        'first_name' => $person,
        'display_name' => $company,
        'role'       => 'zaito_company',
    ) );
    if ( is_wp_error( $user_id ) ) {
        zaito_store_form_errors_and_redirect( array( $user_id->get_error_message() ), $back );
    }
    update_user_meta( $user_id, 'company_name', $company );
    update_user_meta( $user_id, 'company_phone', $phone );
    update_user_meta( $user_id, 'company_url', $site );
    update_user_meta( $user_id, 'zaito_company_status', 'pending' );
    update_user_meta( $user_id, 'zaito_company_registered', current_time( 'mysql' ) );
    update_user_meta( $user_id, 'zaito_email_verified', '0' );
    zaito_send_verification_email( $user_id );

    wp_mail(
        zaito_direct_ops_email(),
        '【zaito運営】企業の登録がありました（審査待ち）: ' . $company,
        $company . "\n" . $site . "\n担当: " . $person . "\n\n" . admin_url( 'admin.php?page=zaito-direct&tab=companies' )
    );

    zaito_log_user_in_silently( $user_id );
    wp_safe_redirect( add_query_arg( 'welcome', '1', home_url( '/company/' ) ) );
    exit;
}
add_action( 'admin_post_nopriv_zaito_register_company', 'zaito_handle_register_company' );
add_action( 'admin_post_zaito_register_company', 'zaito_handle_register_company' );

/* ---------- 会社情報 ---------- */

function zaito_handle_update_company_profile() {
    check_admin_referer( 'zaito_update_company_profile' );
    $user = zaito_require_company();
    $back = home_url( '/company-profile/' );

    $company = zaito_direct_post_text( 'company_name', 80 );
    $person  = zaito_direct_post_text( 'contact_person', 40 );
    $phone   = zaito_direct_post_text( 'phone', 30 );
    $site    = isset( $_POST['company_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['company_url'] ) ) ) : '';
    $auto    = zaito_direct_post_textarea( 'auto_reply_message', 1000 );

    $errors = array();
    if ( '' === $company ) {
        $errors[] = '会社名を入力してください';
    }
    if ( '' === $person ) {
        $errors[] = 'ご担当者名を入力してください';
    }
    if ( '' === $phone ) {
        $errors[] = '電話番号を入力してください';
    }
    if ( $errors ) {
        zaito_store_form_errors_and_redirect( $errors, $back );
    }
    wp_update_user( array( 'ID' => $user->ID, 'first_name' => $person, 'display_name' => $company ) );
    update_user_meta( $user->ID, 'company_name', $company );
    update_user_meta( $user->ID, 'company_phone', $phone );
    update_user_meta( $user->ID, 'company_url', $site );
    update_user_meta( $user->ID, 'auto_reply_message', $auto );
    wp_safe_redirect( add_query_arg( 'saved', '1', $back ) );
    exit;
}
add_action( 'admin_post_zaito_update_company_profile', 'zaito_handle_update_company_profile' );

/* ---------- 求人 ---------- */

/** 求人のフォームの値（保存済みの求人か、エラーで戻ったときの入力）。 */
function zaito_direct_job_form_values( $job_id = 0 ) {
    $v = array(
        'title' => '', 'category' => '', 'summary' => '', 'content' => '', 'contract' => '',
        'pay_type' => '時給', 'pay_min' => '', 'pay_max' => '', 'hours' => '', 'target' => '',
        'tags' => array(), 'question' => '', 'auto_reply' => '', 'confirm_remote' => false, 'confirm_fee' => false,
    );
    if ( $job_id ) {
        $p = get_post( $job_id );
        $v['title']          = $p->post_title;
        $v['content']        = $p->post_content;
        $v['category']       = (string) get_post_meta( $job_id, '_job_category', true );
        $v['summary']        = (string) get_post_meta( $job_id, '_job_summary', true );
        $v['contract']       = (string) get_post_meta( $job_id, '_job_employment_type', true );
        $v['pay_type']       = (string) get_post_meta( $job_id, '_job_salary_type', true );
        $v['pay_min']        = (string) get_post_meta( $job_id, '_job_salary', true );
        $v['pay_max']        = (string) get_post_meta( $job_id, '_job_salary_max', true );
        $v['hours']          = (string) get_post_meta( $job_id, '_job_hours', true );
        $v['target']         = (string) get_post_meta( $job_id, '_job_target_text', true );
        $tags                = get_post_meta( $job_id, '_job_tags', true );
        $v['tags']           = is_array( $tags ) ? $tags : array();
        $v['question']       = (string) get_post_meta( $job_id, '_job_screening_question', true );
        $v['auto_reply']     = (string) get_post_meta( $job_id, '_job_auto_reply_message', true );
        $v['confirm_remote'] = (bool) get_post_meta( $job_id, '_job_confirm_remote', true );
        $v['confirm_fee']    = (bool) get_post_meta( $job_id, '_job_confirm_fee', true );
    }
    return $v;
}

/** 企業が自分の直接掲載の求人を持っているか確かめて、その投稿を返す。 */
function zaito_direct_owned_job( $job_id, $user_id ) {
    $p = $job_id ? get_post( $job_id ) : null;
    if ( ! $p || 'job_listing' !== $p->post_type || ! get_post_meta( $job_id, '_zaito_direct', true ) || (int) get_post_meta( $job_id, '_company_user_id', true ) !== (int) $user_id ) {
        return null;
    }
    return $p;
}

function zaito_handle_direct_save_job() {
    check_admin_referer( 'zaito_direct_save_job' );
    $user   = zaito_require_company();
    $job_id = isset( $_POST['job_id'] ) ? (int) $_POST['job_id'] : 0;
    if ( $job_id && ! zaito_direct_owned_job( $job_id, $user->ID ) ) {
        wp_safe_redirect( home_url( '/company-jobs/' ) );
        exit;
    }
    $submit = isset( $_POST['do'] ) && 'submit' === $_POST['do'];
    $back   = add_query_arg( $job_id ? array( 'edit' => $job_id ) : array( 'new' => 1 ), home_url( '/company-jobs/' ) );

    $title    = zaito_direct_post_text( 'title', 60 );
    $category = zaito_direct_post_text( 'category' );
    $summary  = zaito_direct_post_text( 'summary', 120 );
    $content  = zaito_direct_post_textarea( 'content', 6000 );
    $contract = zaito_direct_post_text( 'contract' );
    $pay_type = zaito_direct_post_text( 'pay_type' );
    $pay_min  = (int) preg_replace( '/[^\d]/', '', zaito_direct_post_text( 'pay_min', 12 ) );
    $pay_max  = (int) preg_replace( '/[^\d]/', '', zaito_direct_post_text( 'pay_max', 12 ) );
    $hours    = zaito_direct_post_text( 'hours', 120 );
    $target   = zaito_direct_post_text( 'target', 160 );
    $question = zaito_direct_post_text( 'question', 200 );
    $auto     = zaito_direct_post_textarea( 'auto_reply', 1000 );
    $tags     = isset( $_POST['tags'] ) && is_array( $_POST['tags'] ) ? array_values( array_intersect( array_map( 'sanitize_key', wp_unslash( $_POST['tags'] ) ), array_keys( zaito_direct_tag_options() ) ) ) : array();
    $c_remote = ! empty( $_POST['confirm_remote'] );
    $c_fee    = ! empty( $_POST['confirm_fee'] );

    if ( ! in_array( $category, zaito_direct_categories(), true ) ) {
        $category = '';
    }
    if ( ! in_array( $contract, zaito_direct_contracts(), true ) ) {
        $contract = '';
    }
    if ( ! in_array( $pay_type, zaito_direct_pay_types(), true ) ) {
        $pay_type = '';
    }

    $errors = array();
    if ( '' === $title ) {
        $errors[] = '求人のタイトルを入力してください';
    }
    if ( $submit ) {
        if ( '' === $category ) {
            $errors[] = '職種を選んでください';
        }
        if ( '' === $summary ) {
            $errors[] = '仕事内容のひとことを入力してください';
        }
        if ( '' === $content ) {
            $errors[] = '仕事内容を入力してください';
        }
        if ( '' === $contract ) {
            $errors[] = '契約の形を選んでください';
        }
        if ( '' === $pay_type || $pay_min <= 0 ) {
            $errors[] = '報酬を入力してください（金額は必須です）';
        } elseif ( $pay_max && $pay_max < $pay_min ) {
            $errors[] = '報酬の上限は下限より大きくしてください';
        }
        if ( '' === $hours ) {
            $errors[] = '稼働条件（日数・時間）を入力してください';
        }
        if ( '' === $target ) {
            $errors[] = '応募できる人を入力してください';
        }
        if ( ! $c_remote ) {
            $errors[] = '面接・初日・研修・会議を含めて出社がないことを確認してください';
        }
        if ( ! $c_fee ) {
            $errors[] = '内定時に成果報酬が発生することへの同意が必要です';
        }
    }

    $postarr = array(
        'post_type'    => 'job_listing',
        'post_title'   => $title ? $title : '（無題の求人）',
        'post_content' => $content,
        'post_status'  => 'draft', // 公開は運営の承認のとき（zaito_direct_admin_job_action）。
    );
    if ( $job_id ) {
        $postarr['ID'] = $job_id;
        $job_id        = wp_update_post( $postarr );
    } else {
        $postarr['post_author'] = $user->ID;
        $job_id                 = wp_insert_post( $postarr );
        if ( $job_id && ! is_wp_error( $job_id ) ) {
            update_post_meta( $job_id, '_zaito_direct', '1' );
            update_post_meta( $job_id, '_company_user_id', $user->ID );
        }
    }
    if ( ! $job_id || is_wp_error( $job_id ) ) {
        zaito_store_form_errors_and_redirect( array( '保存できませんでした。もう一度お試しください。' ), $back );
    }
    $back = add_query_arg( 'edit', $job_id, home_url( '/company-jobs/' ) );

    foreach ( array(
        '_company_name'           => zaito_company_name( $user->ID ),
        '_job_category'           => $category,
        '_job_summary'            => $summary,
        '_job_employment_type'    => $contract,
        '_job_salary_type'        => $pay_type,
        '_job_salary'             => $pay_min ? $pay_min : '',
        '_job_salary_max'         => $pay_max ? $pay_max : '',
        '_job_hours'              => $hours,
        '_job_target_text'        => $target,
        '_job_tags'               => $tags,
        '_job_screening_question' => $question,
        '_job_auto_reply_message' => $auto,
        '_job_confirm_remote'     => $c_remote ? '1' : '',
        '_job_confirm_fee'        => $c_fee ? '1' : '',
    ) as $k => $val ) {
        update_post_meta( $job_id, $k, $val );
    }

    if ( $errors ) {
        update_post_meta( $job_id, '_zaito_review', 'draft' );
        zaito_store_form_errors_and_redirect( $errors, $back );
    }

    if ( $submit ) {
        // 掲載中の求人を直したときも、もう一度審査してから公開する。
        update_post_meta( $job_id, '_zaito_review', 'pending' );
        update_post_meta( $job_id, '_zaito_submitted_at', current_time( 'mysql' ) );
        delete_post_meta( $job_id, '_zaito_review_note' );
        wp_mail(
            zaito_direct_ops_email(),
            '【zaito運営】求人の審査依頼: ' . zaito_company_name( $user->ID ) . '「' . $title . '」',
            admin_url( 'admin.php?page=zaito-direct&tab=jobs' )
        );
        wp_safe_redirect( add_query_arg( 'submitted', '1', home_url( '/company-jobs/' ) ) );
        exit;
    }
    update_post_meta( $job_id, '_zaito_review', 'draft' );
    wp_safe_redirect( add_query_arg( 'saved', '1', $back ) );
    exit;
}
add_action( 'admin_post_zaito_direct_save_job', 'zaito_handle_direct_save_job' );

/** 掲載の終了と再開。再開しても、承認済みの内容ならそのまま公開に戻る。 */
function zaito_handle_direct_close_job() {
    $user   = zaito_require_company();
    $job_id = isset( $_POST['job_id'] ) ? (int) $_POST['job_id'] : 0;
    check_admin_referer( 'zaito_direct_close_job_' . $job_id );
    if ( zaito_direct_owned_job( $job_id, $user->ID ) ) {
        if ( get_post_meta( $job_id, '_zaito_closed', true ) ) {
            delete_post_meta( $job_id, '_zaito_closed' );
        } else {
            update_post_meta( $job_id, '_zaito_closed', current_time( 'mysql' ) );
        }
    }
    wp_safe_redirect( home_url( '/company-jobs/' ) );
    exit;
}
add_action( 'admin_post_zaito_direct_close_job', 'zaito_handle_direct_close_job' );
