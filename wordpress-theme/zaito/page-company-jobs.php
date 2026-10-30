<?php
/**
 * 企業の求人（/company-jobs/）。一覧と、作成・編集のフォーム（?new=1 / ?edit={ID}）。
 * 保存は inc/direct-company.php の zaito_handle_direct_save_job。審査に出すと、運営が承認するまで公開されない。
 */
require_once get_template_directory() . '/lp/biz-parts.php';
$zaito_user = zaito_require_company();
$zaito_edit = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0;
$zaito_new  = isset( $_GET['new'] );
if ( $zaito_edit && ! zaito_direct_owned_job( $zaito_edit, $zaito_user->ID ) ) {
    wp_safe_redirect( home_url( '/company-jobs/' ) );
    exit;
}
$zaito_form = $zaito_edit || $zaito_new;
zaito_biz_head( $zaito_form ? '求人の作成・編集' : '求人' );
?>
<body>
<?php zaito_biz_header( 'jobs' ); ?>
<main class="wrap bz">
<?php if ( ! $zaito_form ) : ?>
  <?php $zaito_jobs = zaito_biz_jobs( $zaito_user->ID ); ?>
  <div class="biz-title"><div><h1>求人</h1><p>求人の作成・編集と、掲載の終了ができます。</p></div>
    <a class="btn btn-p" href="<?php echo esc_url( home_url( '/company-jobs/?new=1' ) ); ?>"><span class="ms" aria-hidden="true">add</span>求人を作成する</a></div>
  <?php if ( isset( $_GET['submitted'] ) ) : ?>
    <div class="note n-ok"><span class="ms" aria-hidden="true">check_circle</span><div><b>審査に出しました</b><p>運営が内容を確認して公開します（通常1〜2営業日）。直してほしい点があれば、メールでお知らせします。</p></div></div>
  <?php endif; ?>
  <?php zaito_biz_account_notes( $zaito_user->ID ); ?>

  <?php if ( $zaito_jobs ) : ?>
    <ul class="rows">
      <?php foreach ( $zaito_jobs as $zaito_p ) : ?>
        <?php
        $zaito_s    = zaito_direct_job_state( $zaito_p );
        $zaito_apps = count( get_posts( array( 'post_type' => 'zaito_application', 'post_status' => 'any', 'posts_per_page' => 500, 'fields' => 'ids', 'meta_query' => array( array( 'key' => 'job_id', 'value' => $zaito_p->ID ) ) ) ) );
        $zaito_note = 'returned' === $zaito_s[0] ? (string) get_post_meta( $zaito_p->ID, '_zaito_review_note', true ) : '';
        ?>
        <li>
          <div class="main"><b><a href="<?php echo esc_url( home_url( '/company-jobs/?edit=' . $zaito_p->ID ) ); ?>"><?php echo esc_html( $zaito_p->post_title ); ?></a></b>
            <span class="rsub"><?php echo esc_html( zaito_direct_pay_text( $zaito_p->ID ) ); ?>　／　応募 <?php echo (int) $zaito_apps; ?>件<?php echo $zaito_note ? '　／　差し戻しの理由: ' . esc_html( $zaito_note ) : ''; ?></span></div>
          <?php echo zaito_biz_status_pill( $zaito_s[0], $zaito_s[1] ); // エスケープ済み ?>
          <div class="acts">
            <?php if ( 'open' === $zaito_s[0] ) : ?>
              <a class="btn btn-g" href="<?php echo esc_url( home_url( '/remote/' . zaito_direct_slug( $zaito_p->ID ) . '/' ) ); ?>" target="_blank" rel="noopener">公開ページ</a>
            <?php endif; ?>
            <a class="btn btn-g" href="<?php echo esc_url( home_url( '/company-jobs/?edit=' . $zaito_p->ID ) ); ?>">編集</a>
            <?php if ( 'open' === $zaito_s[0] || 'closed' === $zaito_s[0] ) : ?>
              <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo 'open' === $zaito_s[0] ? ' onsubmit="return confirm(\'この求人の掲載を終了しますか？\')"' : ''; ?>>
                <input type="hidden" name="action" value="zaito_direct_close_job"><input type="hidden" name="job_id" value="<?php echo (int) $zaito_p->ID; ?>">
                <?php wp_nonce_field( 'zaito_direct_close_job_' . $zaito_p->ID ); ?>
                <button class="btn btn-g" type="submit"><?php echo 'open' === $zaito_s[0] ? '掲載を終了' : '掲載を再開'; ?></button>
              </form>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else : ?>
    <div class="rows"><div class="empty" style="flex-direction:column">まだ求人がありません。<br><a class="btn btn-p" href="<?php echo esc_url( home_url( '/company-jobs/?new=1' ) ); ?>">最初の求人を作成する</a></div></div>
  <?php endif; ?>

<?php else : ?>
  <?php
  $zaito_v     = zaito_direct_job_form_values( $zaito_edit );
  $zaito_state = $zaito_edit ? zaito_direct_job_state( get_post( $zaito_edit ) ) : array( 'draft', '下書き' );
  ?>
  <p class="crumb" style="margin:0 0 8px"><a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>">求人</a> ／ <?php echo $zaito_edit ? '編集' : '新しい求人'; ?></p>
  <div class="biz-title"><div><h1><?php echo $zaito_edit ? '求人を編集' : '求人を作成'; ?></h1><p>入力した内容は、運営の審査のあとに公開されます。</p></div><?php echo zaito_biz_status_pill( $zaito_state[0], $zaito_state[1] ); // エスケープ済み ?></div>
  <?php zaito_biz_errors(); ?>
  <?php if ( isset( $_GET['saved'] ) ) : ?>
    <div class="note n-ok"><span class="ms" aria-hidden="true">check_circle</span><div><b>下書きを保存しました</b><p>公開するには、いちばん下の「審査に出す」を押してください。</p></div></div>
  <?php endif; ?>
  <?php if ( 'open' === $zaito_state[0] ) : ?>
    <div class="note n-warn"><span class="ms" aria-hidden="true">info</span><div><b>掲載中の求人です</b><p>内容を直して保存すると、いったん非公開になります。もう一度「審査に出す」を押し、運営の確認が終わると再び公開されます。すでに届いている応募とメッセージは、そのまま続けられます。</p></div></div>
  <?php elseif ( 'returned' === $zaito_state[0] ) : ?>
    <div class="note n-warn"><span class="ms" aria-hidden="true">info</span><div><b>運営から差し戻しがありました</b><p><?php echo esc_html( get_post_meta( $zaito_edit, '_zaito_review_note', true ) ); ?></p></div></div>
  <?php endif; ?>

  <form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="zaito_direct_save_job">
    <input type="hidden" name="job_id" value="<?php echo (int) $zaito_edit; ?>">
    <?php wp_nonce_field( 'zaito_direct_save_job' ); ?>

    <div class="fcard">
      <h2>仕事の内容</h2>
      <p>求人の一覧と詳細ページに表示されます。</p>
      <div class="fld"><label for="ztitle">求人のタイトル<span class="req">必須</span></label><input type="text" id="ztitle" name="title" maxlength="60" value="<?php echo esc_attr( $zaito_v['title'] ); ?>" placeholder="例：自社メディアの記事作成アシスタント" required><p class="hint">60文字まで。どんな仕事かが伝わる名前にしてください。</p></div>
      <fieldset class="fld"><legend>職種<span class="req">必須</span></legend><div class="picks">
        <?php foreach ( zaito_direct_categories() as $zaito_o ) : ?>
          <label><input type="radio" name="category" value="<?php echo esc_attr( $zaito_o ); ?>"<?php checked( $zaito_v['category'], $zaito_o ); ?>><span><?php echo esc_html( $zaito_o ); ?></span></label>
        <?php endforeach; ?>
      </div></fieldset>
      <div class="fld"><label for="zsummary">仕事内容のひとこと<span class="req">必須</span></label><input type="text" id="zsummary" name="summary" maxlength="120" value="<?php echo esc_attr( $zaito_v['summary'] ); ?>" placeholder="例：SNSで集めた読者の声をもとに、記事の構成づくりと下書きを担当します。"><p class="hint">一覧のカードに出ます（120文字まで）。</p></div>
      <div class="fld"><label for="zcontent">仕事内容<span class="req">必須</span></label><textarea id="zcontent" name="content" rows="10" placeholder="具体的な業務、1日の流れ、使うツール、身につくこと、チームの体制など"><?php echo esc_textarea( $zaito_v['content'] ); ?></textarea></div>
    </div>

    <div class="fcard">
      <h2>条件</h2>
      <p>報酬は金額まで書いてください。学生が一番よく見る項目です。</p>
      <fieldset class="fld"><legend>契約の形<span class="req">必須</span></legend><div class="picks">
        <?php foreach ( zaito_direct_contracts() as $zaito_o ) : ?>
          <label><input type="radio" name="contract" value="<?php echo esc_attr( $zaito_o ); ?>"<?php checked( $zaito_v['contract'], $zaito_o ); ?>><span><?php echo esc_html( $zaito_o ); ?></span></label>
        <?php endforeach; ?>
      </div></fieldset>
      <div class="fld"><span class="lb">報酬<span class="req">必須</span></span>
        <div class="row">
          <select name="pay_type" aria-label="報酬の単位">
            <?php foreach ( zaito_direct_pay_types() as $zaito_o ) : ?>
              <option<?php selected( $zaito_v['pay_type'], $zaito_o ); ?>><?php echo esc_html( $zaito_o ); ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" inputmode="numeric" name="pay_min" value="<?php echo esc_attr( $zaito_v['pay_min'] ); ?>" placeholder="1300" aria-label="下限（円）">
          <span class="unit">〜</span>
          <input type="text" inputmode="numeric" name="pay_max" value="<?php echo esc_attr( $zaito_v['pay_max'] ); ?>" placeholder="上限（任意）" aria-label="上限（円・任意）">
          <span class="unit">円</span>
        </div>
      </div>
      <div class="fld"><label for="zhours">稼働条件<span class="req">必須</span></label><input type="text" id="zhours" name="hours" maxlength="120" value="<?php echo esc_attr( $zaito_v['hours'] ); ?>" placeholder="例：週2日・1日3時間から、平日10〜19時の間（土日も可）"></div>
      <div class="fld"><label for="ztarget">応募できる人<span class="req">必須</span></label><input type="text" id="ztarget" name="target" maxlength="160" value="<?php echo esc_attr( $zaito_v['target'] ); ?>" placeholder="例：大学生・大学院生（全学年）。パソコンの基本操作ができる方"></div>
      <fieldset class="fld"><legend>あてはまる特徴<span class="opt">任意</span></legend><div class="picks">
        <?php foreach ( zaito_direct_tag_options() as $zaito_k => $zaito_o ) : ?>
          <label><input type="checkbox" name="tags[]" value="<?php echo esc_attr( $zaito_k ); ?>"<?php checked( in_array( $zaito_k, $zaito_v['tags'], true ) ); ?>><span><?php echo esc_html( $zaito_o ); ?></span></label>
        <?php endforeach; ?>
      </div><p class="hint">一覧の絞り込みに使われます。</p></fieldset>
    </div>

    <div class="fcard">
      <h2>応募の受け付け</h2>
      <p>応募はzaitoに届き、応募者とはzaitoのメッセージでやりとりします。</p>
      <div class="fld"><label for="zquestion">応募者への質問<span class="opt">任意</span></label><input type="text" id="zquestion" name="question" maxlength="200" value="<?php echo esc_attr( $zaito_v['question'] ); ?>" placeholder="例：週に何時間くらい働けそうですか？"><p class="hint">応募フォームに1問だけ追加できます。</p></div>
      <div class="fld"><label for="zauto">応募直後の自動メッセージ<span class="opt">任意</span></label><textarea id="zauto" name="auto_reply" rows="4" placeholder="例：ご応募ありがとうございます。2営業日以内に、面談の日程についてご連絡します。"><?php echo esc_textarea( $zaito_v['auto_reply'] ); ?></textarea><p class="hint">空欄なら、「会社情報」で決めた文面を送ります。そちらも空欄なら、自動メッセージは送りません。</p></div>
    </div>

    <div class="fcard">
      <h2>掲載前の確認</h2>
      <p>審査に出すには、2つとも確認が必要です。</p>
      <label class="check"><input type="checkbox" name="confirm_remote" value="1"<?php checked( $zaito_v['confirm_remote'] ); ?>><span><b>面接・初日・研修・会議も含めて、出社は一切ありません</b><small>居住地の制限がある場合は「応募できる人」に書いてください。出社があると分かった場合は、掲載を止めることがあります。</small></span></label>
      <label class="check"><input type="checkbox" name="confirm_fee" value="1"<?php checked( $zaito_v['confirm_fee'] ); ?>><span><b>zaitoからの応募者に内定を出した時点で、成果報酬が発生することに同意します</b><small>掲載や応募の受け付けに費用はかかりません。金額は、求人の公開前に運営からご案内します。</small></span></label>
    </div>

    <div class="form-acts">
      <button type="submit" name="do" value="submit" class="btn btn-p btn-lg">審査に出す</button>
      <button type="submit" name="do" value="draft" class="btn btn-g btn-lg" formnovalidate>下書き保存</button>
      <span class="sp"></span>
      <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>">一覧に戻る</a>
    </div>
  </form>
<?php endif; ?>
</main>
<?php zaito_biz_footer(); ?>
</body>
</html>
