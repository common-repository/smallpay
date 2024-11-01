<?php
wp_enqueue_style('smallpay_style', plugins_url('assets/css/smallpay.css', plugin_dir_path(__FILE__)));
//include_once plugins_url("includes/constant_smallpay.php", plugin_dir_path(__FILE__));
?>
<div id="sp-order-details" class="panel">
    <div class="order_data_column_container">
        <div style="width:100%;">
            <?php
            if (is_object($aOrderInstallments) && count($aOrderInstallments->installments) > 1) {
                ?>
                <h3><?php echo __('Installments Information', 'smallpay'); ?></h3>
                <div class="woocommerce_subscriptions_related_orders">
                    <table class="wp-list-table widefat fixed striped table-view-list posts">
                        <thead>
                            <tr>
                                <th><?php echo __('Nr.', 'smallpay') ?></th>
                                <th><?php echo __('Amount', 'smallpay') ?></th>
                                <th><?php echo __('Scheduled date', 'smallpay') ?></th>
                                <th><?php echo __('Payment Date', 'smallpay') ?></th>
                                <th><?php echo __('Paid', 'smallpay') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($aOrderInstallments->installments as $i => $set) {
                                ?>
                                <tr>
                                    <td><?php echo $i == 0 ? __('First payment', 'smallpay') : $i ?></td>
                                    <td><?php echo '€ ' . number_format($set->amount, 2, ',', '') ?></td>
                                    <td>
                                        <?php
                                        $expDate = new DateTime($set->payableBy);
                                        echo $expDate->format("d/m/Y");
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($set->transactionDate != null) {
                                            $actDate = new DateTime($set->transactionDate);
                                            echo $actDate->format("d/m/Y");
                                        }
                                        ?>
                                    </td>
                                    <td>
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

                    <br />

                    <?php echo ' <a href="' . $aOrderInstallments->urlContract . '" target="_blank">' . __('Download your contract', 'smallpay') . '</a> ' . __('for the installment plan with SmallPay', 'smallpay'); ?>

                    <br /><br />
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
