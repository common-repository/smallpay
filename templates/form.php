<div id="smallpay-form-container">
    <?php get_template_part(plugin_dir_path(__FILE__) . "includes/constant_smallpay.php"); ?>

    <?php
    if ($installmentsInfo['pay_ins']) {
        ?>
        <div id="installment-block">
            <p class="smallpay-p-size">
                <?php echo __('Split the payment amount, choose the number of installments (from', 'smallpay') . ' ' . $installmentsInfo['min'] . ' ' . __('to', 'smallpay') . ' ' . $installmentsInfo['max'] . __(') and insert your credit card! You will be charged on the first day of each month until the due date.', 'smallpay'); ?>
            </p>

            <br>

            <div id="custom-checkbox-container">
                <input type="checkbox" id="smallpay_accept_check" name="accept" value="accept">
                <span id='smallpay_accept_text' class="smallpay-p-size">
                    <?php echo __('I confirm that I have read and accepted the', 'smallpay') . ' <a href="' . site_url() . '/?tos=1" target="_blank">' . __('contract terms and conditions', 'smallpay') . '</a>'; ?>
                </span>

                <br>
            </div>


            <p id="smallpay-installments-number-title" class="smallpay-select smallpay-p-size"><?php echo __('Choose the number of installments', 'smallpay'); ?></p>
            <select id="smallpay-installments-number" style="display: inline;">
                <?php
                for ($i = $installmentsInfo['min']; $i <= $installmentsInfo['max']; $i++) {
                    if ($i == 1) {
                        echo '<option value="1">' . __('Single solution', 'smallpay') . '</option>';
                    } else {
                        echo '<option value="' . $i . '"' . ($i == $installmentsInfo['max'] ? ' selected="selected">' : '>') . $i . '</option>';
                    }
                }
                ?>
            </select>

            <br class="br-remove"><br class="br-remove">

            <div id="smallpay-intallment-info">
                <?php include_once $path . 'templates/installments_plan.php'; ?> 
            </div>
        </div>
        <?php
    } else {
        echo __('Pay by credit card via SmallPay', 'smallpay');
    }
    ?>

    <input type="hidden" id="smallpay_admin_url" value="<?php echo admin_url() ?>">
    <input type="hidden" value="<?php echo $installmentsInfo['max'] ?>" id="smallpay_xpay_maxInstallments">
    <input type="hidden" name="oneInstallmentText" id="oneInstallmentText" value="<?php echo __('You chose to make the payment in a single solution of €', 'smallpay') ?>">
    <input type="hidden" name="installments" id="installments" value="<?php echo $installmentsInfo['max'] ?>">
    <input type="hidden" id="total-formated" value="<?php echo $totalFormatted ?>">
</div>