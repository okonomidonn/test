<?php
if ( ! is_user_logged_in() ) {
    wp_redirect( home_url( '/login/' ) );
    exit;
}
$current_user = wp_get_current_user();
if ( ! zaito_can_use_seeker_features( $current_user ) ) {
    wp_redirect( home_url( '/' ) );
    exit;
}

$profile_errors = zaito_get_form_errors_from_token();
$profile_saved = isset( $_GET['saved'] ) && $_GET['saved'] === '1';
$profile_redirect_to = ! empty( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';

$furigana = get_user_meta( $current_user->ID, 'furigana', true );
$birthdate = get_user_meta( $current_user->ID, 'birthdate', true );
$phone = get_user_meta( $current_user->ID, 'phone', true );
$prefecture = get_user_meta( $current_user->ID, 'prefecture', true );
$education = get_user_meta( $current_user->ID, 'education', true );
$work_history = get_user_meta( $current_user->ID, 'work_history', true );

get_header();

$prefectures = array( '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県' );
$education_options = array( '中学校卒業', '高等学校卒業', '高等学校在学中', '専門学校卒業', '専門学校在学中', '短期大学卒業', '短期大学在学中', '大学卒業', '大学在学中', '大学院卒業', '大学院在学中', 'その他' );
?>

<main class="mypage-main">
  <div class="wrap">
    <div class="mypage-header">
      <h1>プロフィール編集</h1>
      <p>応募時に企業へ共有される情報です。正確にご入力ください。</p>
    </div>

    <div class="apply-container">
      <div class="apply-card">
        <?php if ( $profile_saved ) : ?>
          <div class="auth-error" style="background:#f1fffb;color:#087f70;"><p>プロフィールを保存しました</p></div>
        <?php endif; ?>

        <?php if ( ! empty( $profile_errors ) ) : ?>
          <div class="auth-error">
            <?php foreach ( $profile_errors as $error ) : ?>
              <p><?php echo esc_html( $error ); ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="apply-form">
          <input type="hidden" name="action" value="zaito_update_worker_profile" />
          <?php if ( $profile_redirect_to ) : ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $profile_redirect_to ); ?>" />
          <?php endif; ?>
          <?php wp_nonce_field( 'zaito_update_worker_profile' ); ?>

          <p style="font-size:13px;color:var(--muted);margin:-8px 0 16px;">
            <span style="color:#d34e79;">*</span> は必須項目です。求人への応募にはすべての必須項目の入力が必要です。
          </p>

          <div class="form-group">
            <label for="name">氏名 <span style="color:#d34e79;">*</span></label>
            <input type="text" id="name" name="name" value="<?php echo esc_attr( $current_user->first_name ); ?>" required />
          </div>

          <div class="form-group">
            <label for="furigana">フリガナ <span style="color:#d34e79;">*</span></label>
            <input type="text" id="furigana" name="furigana" value="<?php echo esc_attr( $furigana ); ?>" placeholder="ヤマダ タロウ" required />
          </div>

          <div class="job-info-grid" style="margin-bottom:20px;">
            <div class="form-group" style="margin-bottom:0;">
              <label for="birthdate">生年月日 <span style="color:#d34e79;">*</span></label>
              <input type="date" id="birthdate" name="birthdate" value="<?php echo esc_attr( $birthdate ); ?>" required />
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label for="phone">電話番号 <span style="color:#d34e79;">*</span></label>
              <input type="tel" id="phone" name="phone" value="<?php echo esc_attr( $phone ); ?>" placeholder="090-1234-5678" required />
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label for="prefecture">お住まいの都道府県 <span style="color:#d34e79;">*</span></label>
              <select id="prefecture" name="prefecture" required>
                <option value="">選択してください</option>
                <?php foreach ( $prefectures as $pref ) : ?>
                  <option value="<?php echo esc_attr( $pref ); ?>" <?php selected( $prefecture, $pref ); ?>><?php echo esc_html( $pref ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label for="education">最終学歴 <span style="color:#d34e79;">*</span></label>
              <select id="education" name="education" required>
                <option value="">選択してください</option>
                <?php foreach ( $education_options as $edu ) : ?>
                  <option value="<?php echo esc_attr( $edu ); ?>" <?php selected( $education, $edu ); ?>><?php echo esc_html( $edu ); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="work_history">職務経歴・自己PR</label>
            <textarea id="work_history" name="work_history" rows="8" placeholder="これまでの職務経験やスキル、アピールポイントをご記入ください"><?php echo esc_textarea( $work_history ); ?></textarea>
            <small>応募時に企業へ表示されます（任意項目です）。</small>
          </div>

          <button type="submit" class="btn btn-accent">保存する</button>
        </form>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
