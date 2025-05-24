<?php

use Hostinger\AiTheme\Constants\PreviewImageConstant;

?>
<div class="hostinger-featured-image-wrapper">
    <p><?php echo __( 'External image can be used to show featured image.', 'hostinger-ai-theme' ); ?></p>
    <input
        id="<?php echo PreviewImageConstant::META_SLUG; ?>"
        type="text"
        name="<?php echo PreviewImageConstant::META_SLUG; ?>"
        placeholder="<?php echo __( 'External Image URL', 'hostinger-ai-theme' ); ?>"
        value="<?php echo esc_url( $image_url ); ?>"
    />
</div>