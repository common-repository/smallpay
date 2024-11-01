<div class="smallpay-widget">
    <div style="border-bottom: 1px solid #eee;margin: 0 -12px;padding: 8px 12px 4px;">
        <img src="<?php echo $logo ?>" style="margin-left: auto;margin-right: auto;display: block;padding-bottom: 10px;">
    </div>
    <ul class="wc_status_list" style="overflow: hidden">
        <li style="margin: 0 -12px;padding: 8px 12px 4px;width:50%;display: block;float:left;text-align:center;">
            <h1><strong><span style="color:green;font-size:50px;"><?php echo $ok ?></span></strong></h1>
            <?php echo $okText ?>
        </li>
        <li style="margin: 0 -12px;padding: 8px 12px 4px;width:50%;display: block;float:left;text-align:center;">
            <h1><strong><span style="color:red;font-size:50px;"><?php echo $ko ?></span></strong></h1>
            <?php echo $koText ?>
        </li>
        <li style="margin: 0 -12px;padding: 8px 12px 4px;width:100%;display: block;float:left;text-align:center;margin-top: 20px;">
            <h1><strong><span style="color:black;font-size:50px;"><?php echo $completed ?></span></strong></h1>
                    <?php echo $completedText ?>
        </li>
    </ul>
</div>
