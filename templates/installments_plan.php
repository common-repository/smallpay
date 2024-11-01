<?php
if ($installments > 1) {
    $date = new DateTime(date("Y-m-d"));
    ?>

    <p class="smallpay-p-size smallpay-p-confirm"><?php echo __('Your installment plan will be as follows:', 'smallpay') ?></p>
    <table class="shop_table woocommerce-checkout-review-order-table smallpay-table" >
        <tr class="cart_item">
            <th class="product-name smallpay-installment" ><?php echo __('Installment n°', 'smallpay') ?></th>
            <th class="product-total smallpay-amount" ><?php echo __('Installment price', 'smallpay') ?></th>
            <th class="product-name smallpay-date" ><?php echo __('Installment of', 'smallpay') ?></th>
        </tr>
        <tr class="cart_item">
            <td class="product-name smallpay-installment" ><?php echo __('First payment', 'smallpay') ?></td>
            <td class="product-total smallpay-amount" >€ <?php echo $first_installment_amount ?></td>
            <td class="product-name smallpay-date" ><?php echo $date->format('m/Y') ?></td>
        </tr>

        <?php
        for ($inst = 1; $inst < $installments; $inst++) {
            $date->modify('+ 1  month');
            ?>
            <tr class="cart_item">
                <td class="product-name smallpay-installment" ><?php echo $inst ?></td>
                <td class="product-total smallpay-amount" >€ <?php echo $installment_amount ?></td>
                <td class="product-name smallpay-date" ><?php echo $date->format('m/Y') ?></td>
            </tr>
            <?php
        }
        ?>
        <tr class="cart_item">
            <td class="product-name smallpay-total"><?php echo __('Total', 'smallpay') ?></td>
            <td class="product-total smallpay-amount">€ <?php echo $totalFormatted ?></td>
        </tr>
    </table>
    <?php
}
?>