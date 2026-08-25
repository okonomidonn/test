<?php
$interest_errors = zaito_get_form_errors_from_token();
$interest_submitted = isset( $_GET['submitted'] ) && $_GET['submitted'] === '1';
get_header();
?>

<main class="auth-main">
  <div class="auth-container">
    <div class="auth-card">

      <?php if ( $interest_submitted ) : ?>
        <h1>登録ありがとうございます</h1>
        <p style="margin-top:-8px;color:var(--muted);">興味ありリストに登録しました。今後、条件に合いそうな求人が増え次第、メールでお知らせいたします。</p>
        <a href="<?php echo esc_url( home_url( '/jobs/' ) ); ?>" class="btn btn-accent btn-block" style="margin-top:16px;">今の求人一覧を見てみる</a>
      <?php else : ?>
        <h1>興味ありリストに登録</h1>
        <p style="margin-top:-8px;color:var(--muted);">パスワード不要・1分で完了。今すぐ求人に応募するのではなく、「気になる求人が出たら教えてほしい」という方向けの簡単登録です。</p>

        <?php if ( ! empty( $interest_errors ) ) : ?>
          <div class="auth-error">
            <?php foreach ( $interest_errors as $error ) : ?>
              <p><?php echo esc_html( $error ); ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="auth-form">
          <input type="hidden" name="action" value="zaito_submit_interest" />
          <?php wp_nonce_field( 'zaito_submit_interest' ); ?>

          <div class="form-group">
            <label for="name">お名前(ニックネーム可)</label>
            <input type="text" id="name" name="name" required />
          </div>

          <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" required />
          </div>

          <div class="form-group">
            <label>ご興味のある案件(複数選択可)</label>
            <div class="checkbox-group">
              <?php foreach ( array( 'データ入力', 'ライティング', 'SNS運用', 'カスタマーサポート', 'その他' ) as $zaito_interest_opt ) : ?>
                <label class="checkbox-group-item">
                  <input type="checkbox" name="interests[]" value="<?php echo esc_attr( $zaito_interest_opt ); ?>" />
                  <?php echo esc_html( $zaito_interest_opt ); ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="hours">稼働可能時間(週あたり)</label>
            <select id="hours" name="hours">
              <option value="">選択してください</option>
              <?php foreach ( array( '週1〜3時間', '週4〜6時間', '週7〜10時間', '週10時間以上', '応相談' ) as $zaito_hours_opt ) : ?>
                <option value="<?php echo esc_attr( $zaito_hours_opt ); ?>"><?php echo esc_html( $zaito_hours_opt ); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>現在のご状況</label>
            <div class="checkbox-group">
              <?php foreach ( array( '主婦(夫)', '学生', 'その他' ) as $zaito_status_opt ) : ?>
                <label class="checkbox-group-item">
                  <input type="radio" name="status" value="<?php echo esc_attr( $zaito_status_opt ); ?>" />
                  <?php echo esc_html( $zaito_status_opt ); ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-group">
            <label for="memo">ひとことメモ(任意)</label>
            <textarea id="memo" name="memo" rows="3" placeholder="興味のあるお仕事のイメージや、稼働できる曜日・時間帯など"></textarea>
          </div>

          <button type="submit" class="btn btn-accent btn-block">興味ありリストに登録する</button>
        </form>

        <p style="margin-top:16px;font-size:13px;color:var(--muted);">今すぐ本登録したい方は<a href="<?php echo esc_url( home_url( '/register/' ) ); ?>" class="section-link">こちらのワーカー登録</a>からどうぞ。</p>
      <?php endif; ?>

    </div>
  </div>
</main>

<?php get_footer(); ?>
