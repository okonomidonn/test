<?php
/**
 * 企業のホーム（/company/）。アカウントの状態、未読・新着の応募、求人の状態をまとめて見せる。
 */
require_once get_template_directory() . '/lp/biz-parts.php';
$zaito_user = zaito_require_company();
$zaito_c    = zaito_biz_counts( $zaito_user->ID );
$zaito_apps = array_slice( zaito_direct_applications( 'company', $zaito_user->ID ), 0, 8 );
$zaito_lbl  = zaito_direct_app_statuses();
zaito_biz_head( 'ホーム' );
?>
<body>
<?php zaito_biz_header( 'home' ); ?>
<main class="wrap bz">
  <div class="biz-title"><div><h1>ホーム</h1><p><?php echo esc_html( $zaito_user->first_name ); ?> 様、こんにちは。</p></div>
    <a class="btn btn-p" href="<?php echo esc_url( home_url( '/company-jobs/?new=1' ) ); ?>"><span class="ms" aria-hidden="true">add</span>求人を作成する</a></div>

  <?php if ( isset( $_GET['welcome'] ) ) : ?>
    <div class="note n-ok"><span class="ms" aria-hidden="true">check_circle</span><div><b>登録ありがとうございます</b><p>確認メールを送りました。審査を待つあいだに、求人を作成して審査に出せます。</p></div></div>
  <?php endif; ?>
  <?php zaito_biz_account_notes( $zaito_user->ID ); ?>

  <div class="stats4">
    <a href="<?php echo esc_url( home_url( '/company-applicants/?view=unread' ) ); ?>" class="<?php echo $zaito_c['unread'] ? 'hot' : ''; ?>"><span>未読のメッセージ</span><b><?php echo (int) $zaito_c['unread']; ?><small>件</small></b></a>
    <a href="<?php echo esc_url( home_url( '/company-applicants/?view=new' ) ); ?>" class="<?php echo $zaito_c['new'] ? 'hot' : ''; ?>"><span>新着の応募</span><b><?php echo (int) $zaito_c['new']; ?><small>件</small></b></a>
    <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>"><span>掲載中の求人</span><b><?php echo (int) $zaito_c['open']; ?><small>件</small></b></a>
    <a href="<?php echo esc_url( home_url( '/company-jobs/' ) ); ?>"><span>審査待ちの求人</span><b><?php echo (int) $zaito_c['pending']; ?><small>件</small></b></a>
  </div>

  <section class="biz-sec" aria-labelledby="zrecent">
    <h2 id="zrecent">最近の応募</h2>
    <?php if ( $zaito_apps ) : ?>
      <ul class="rows">
        <?php foreach ( $zaito_apps as $zaito_a ) : ?>
          <?php
          $zaito_st  = zaito_direct_app_status( $zaito_a->ID );
          $zaito_job = get_post( (int) get_post_meta( $zaito_a->ID, 'job_id', true ) );
          $zaito_new = (bool) get_post_meta( $zaito_a->ID, 'unread_company', true );
          ?>
          <li>
            <div class="main"><b><a href="<?php echo esc_url( zaito_direct_chat_url( $zaito_a->ID ) ); ?>"><?php if ( $zaito_new ) : ?><span class="dot-new" aria-label="未読"></span><?php endif; ?><?php echo esc_html( get_post_meta( $zaito_a->ID, 'applicant_name', true ) ); ?></a></b>
              <span class="rsub"><?php echo esc_html( zaito_direct_applicant_line( $zaito_a->ID ) ); ?>　／　<?php echo esc_html( $zaito_job ? $zaito_job->post_title : '' ); ?></span></div>
            <span class="meta"><?php echo esc_html( get_the_date( 'n月j日', $zaito_a ) ); ?></span>
            <?php echo zaito_biz_status_pill( $zaito_st, $zaito_lbl[ $zaito_st ] ); // エスケープ済み ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else : ?>
      <div class="rows"><div class="empty" style="flex-direction:column">まだ応募はありません。求人が公開されると、ここに応募が表示されます。</div></div>
    <?php endif; ?>
  </section>

  <section class="biz-sec" aria-labelledby="zhow">
    <h2 id="zhow">zaitoでの採用の流れ</h2>
    <ul class="rows">
      <li><span class="ms" aria-hidden="true" style="color:var(--z-blue)">edit_document</span><div class="main"><b>1. 求人を作成して審査に出す</b><span class="rsub">運営が、面接・初日・研修・会議を含めて出社がないかを確認してから公開します。</span></div></li>
      <li><span class="ms" aria-hidden="true" style="color:var(--z-blue)">forum</span><div class="main"><b>2. 応募者とメッセージでやりとり</b><span class="rsub">日程調整と結果の連絡はzaitoのメッセージで。面接のZoomなどのURLも、メッセージで送れます。</span></div></li>
      <li><span class="ms" aria-hidden="true" style="color:var(--z-blue)">task_alt</span><div class="main"><b>3. 内定を出したら、状態を「内定」に</b><span class="rsub">内定の時点で成果報酬が発生します。掲載や応募の受け付けに費用はかかりません。</span></div></li>
    </ul>
  </section>
</main>
<?php zaito_biz_footer(); ?>
</body>
</html>
