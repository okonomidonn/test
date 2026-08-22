<?php
if ( ! is_user_logged_in() ) {
    wp_redirect( home_url( '/login/' ) );
    exit;
}
get_header();
$current_user = wp_get_current_user();
$conversation_id = isset( $_GET['conversation_id'] ) ? intval( $_GET['conversation_id'] ) : 0;
?>

<main class="chat-main">
  <div class="wrap">
    <div class="chat-container">
      <aside class="chat-sidebar">
        <h2>メッセージ</h2>
        <div class="conversations-list" id="conversations">
          <?php
          $is_seeker_view = in_array( 'zaito_seeker', $current_user->roles, true );

          if ( $is_seeker_view ) {
              $args = array(
                  'post_type' => 'zaito_application',
                  'posts_per_page' => -1,
                  'meta_query' => array(
                      array(
                          'key' => 'applicant_id',
                          'value' => $current_user->ID,
                      ),
                  ),
              );
              $applications = get_posts( $args );
          } else {
              // 企業側は求人の投稿者（_company_user_id）が自分と一致する
              // 応募のみを表示する。ステータスに関わらず表示する
              // （応募直後の自動返信メッセージから見えるようにするため）。
              $all_applications = get_posts( array(
                  'post_type' => 'zaito_application',
                  'posts_per_page' => -1,
              ) );
              $applications = array_values( array_filter( $all_applications, function ( $app ) use ( $current_user ) {
                  return zaito_get_application_company_id( $app->ID ) === $current_user->ID;
              } ) );
          }

          if ( ! empty( $applications ) ) :
              foreach ( $applications as $app ) :
                  if ( $is_seeker_view ) {
                      $other_user_id = zaito_get_application_company_id( $app->ID );
                      $job_id = get_post_meta( $app->ID, 'job_id', true );
                      $other_name = get_post_meta( $job_id, '_company_name', true );
                  } else {
                      $other_user_id = (int) get_post_meta( $app->ID, 'applicant_id', true );
                      $applicant_user = get_user_by( 'id', $other_user_id );
                      $other_name = $applicant_user ? $applicant_user->first_name : '不明';
                  }
                  $last_message = get_post_meta( $app->ID, 'last_message_text', true );
                  $class = ( $conversation_id == $app->ID ) ? 'active' : '';
          ?>
            <a href="<?php echo esc_url( home_url( '/chat/?conversation_id=' . $app->ID ) ); ?>" class="conversation-item <?php echo esc_attr( $class ); ?>" data-conversation-id="<?php echo esc_attr( $app->ID ); ?>">
              <div class="conversation-info">
                <p class="name"><?php echo esc_html( $other_name ); ?></p>
                <p class="preview"><?php echo esc_html( wp_trim_words( $last_message, 15 ) ); ?></p>
              </div>
            </a>
          <?php
              endforeach;
          else :
          ?>
            <p class="empty">メッセージはありません</p>
          <?php endif; ?>
        </div>
      </aside>

      <div class="chat-content">
        <?php if ( $conversation_id ) : ?>
          <?php
          $application = get_post( $conversation_id );
          $applicant_id_of_conv = $application ? (int) get_post_meta( $application->ID, 'applicant_id', true ) : 0;
          $company_id_of_conv = $application ? zaito_get_application_company_id( $application->ID ) : 0;
          $can_view_conversation = $application
              && ( $current_user->ID === $applicant_id_of_conv || $current_user->ID === $company_id_of_conv );

          if ( $application && $application->post_type === 'zaito_application' && $can_view_conversation ) :
              if ( $current_user->ID === $applicant_id_of_conv ) {
                  $other_user_id = $company_id_of_conv;
                  $job_id = get_post_meta( $application->ID, 'job_id', true );
                  $other_name = get_post_meta( $job_id, '_company_name', true );
              } else {
                  $other_user_id = $applicant_id_of_conv;
                  $applicant_user = get_user_by( 'id', $other_user_id );
                  $other_name = $applicant_user ? $applicant_user->first_name : '不明';
              }
          ?>
            <div class="chat-header">
              <h2><?php echo esc_html( $other_name ); ?></h2>
            </div>

            <div class="messages-container" id="messages-container" data-conversation-id="<?php echo esc_attr( $conversation_id ); ?>">
              <!-- メッセージがここに読み込まれます -->
            </div>

            <form class="chat-form" id="chat-form" data-conversation-id="<?php echo esc_attr( $conversation_id ); ?>">
              <textarea
                id="message-input"
                name="message"
                placeholder="メッセージを入力..."
                rows="3"
                required
              ></textarea>
              <button type="submit" class="btn btn-accent">送信</button>
            </form>

            <script>
            (function() {
              const conversationId = <?php echo esc_js( $conversation_id ); ?>;
              const currentUserId = <?php echo esc_js( $current_user->ID ); ?>;

              function loadMessages() {
                jQuery.ajax({
                  url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                  type: 'POST',
                  data: {
                    action: 'zaito_load_messages',
                    conversation_id: conversationId,
                    nonce: '<?php echo wp_create_nonce( 'zaito_chat_nonce' ); ?>'
                  },
                  success: function(response) {
                    if (response.success) {
                      jQuery('#messages-container').html(response.data.html);
                      const container = jQuery('#messages-container');
                      container.scrollTop(container[0].scrollHeight);
                    }
                  }
                });
              }

              jQuery('#chat-form').on('submit', function(e) {
                e.preventDefault();
                const message = jQuery('#message-input').val();
                if (!message.trim()) return;

                jQuery.ajax({
                  url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                  type: 'POST',
                  data: {
                    action: 'zaito_send_message',
                    conversation_id: conversationId,
                    message: message,
                    nonce: '<?php echo wp_create_nonce( 'zaito_chat_nonce' ); ?>'
                  },
                  success: function(response) {
                    if (response.success) {
                      jQuery('#message-input').val('');
                      loadMessages();
                    }
                  }
                });
              });

              loadMessages();
              setInterval(loadMessages, 3000);
            })();
            </script>
          <?php else : ?>
            <div class="empty-state">
              <p>会話を選択してください</p>
            </div>
          <?php endif; ?>
        <?php else : ?>
          <div class="empty-state">
            <p>メッセージを選択してください</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php get_footer(); ?>
