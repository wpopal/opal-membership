<form id="membership-package-purchase-form" action="" method="post">
    <div class="membership-form-wrapper">
        <button class="membership-add-to-purchase btn btn-md btn-block radius-6x btn-3d <?php echo $highlighted ? 'btn-primary' : 'btn-default'; ?>"
                data-id="<?php the_ID(); ?>" data-action="membership_add_to_purchase">
            <span class="membership-label"><?php echo esc_html( $button_text ); ?></span>
        </button>
    </div>
</form>
