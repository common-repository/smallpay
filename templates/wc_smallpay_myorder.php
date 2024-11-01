<?php
wp_enqueue_style('smallpay_style', plugins_url('assets/css/smallpay.css', plugin_dir_path(__FILE__)));
get_template_part(plugin_dir_path(__FILE__) . "includes/constant_smallpay.php");
?>

<?php
if (isset($aOrderInstallments->installments) && count($aOrderInstallments->installments) > 1) {
    ?>
    <h3><?php echo __('Installments Information', 'smallpay'); ?></h3>
    <div class="woocommerce_subscriptions_related_orders">
        <table style="text-align: center; width: 100%;">
            <thead>
                <tr>
                    <th style="text-align: center;"><?php echo __('Nr.', 'smallpay') ?></th>
                    <th style="text-align: center;"><?php echo __('Amount', 'smallpay') ?></th>
                    <th style="text-align: center;"><?php echo __('Scheduled date', 'smallpay') ?></th>
                    <th style="text-align: center;"><?php echo __('Payment Date', 'smallpay') ?></th>
                    <th style="text-align: center;"><?php echo __('Paid', 'smallpay') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($aOrderInstallments->installments as $i => $set) {
                    ?>
                    <tr>
                        <td style="text-align: center;"><?php echo $i == 0 ? __('First payment', 'smallpay') : $i ?></td>
                        <td style="text-align: center;"><?php echo '€ ' . number_format($set->amount, 2, ',', '') ?></td>
                        <td style="text-align: center;">
                            <?php
                            $expDate = new DateTime($set->payableBy);
                            echo $expDate->format("d/m/Y");
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <?php
                            if ($set->transactionDate != null) {
                                $actDate = new DateTime($set->transactionDate);
                                echo $actDate->format("d/m/Y");
                            }
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <?php
                            if ($set->transactionStatus == __SMALLPAY_TS_PAYED__) {
                                echo __SMALLPAY_ICON_OK__;
                            } else {
                                echo __SMALLPAY_ICON_KO__;
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php echo ' <a href="' . $aOrderInstallments->urlContract . '" target="_blank">' . __('Download your contract', 'smallpay') . '</a> ' . __('for the installment plan with SmallPay', 'smallpay'); ?>
        <br><br>
    </div>
    <?php
}
?>
</div>
