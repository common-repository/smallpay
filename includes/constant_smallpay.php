<?php

const __SMALLPAY_ICON_OK__ = '<img src="data:image/png;base64,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" style="align-content: center;display: initial;">';

const __SMALLPAY_ICON_KO__ = '<img src="data:image/png;base64,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" style="align-content: center;display: initial;">';

const __SMALLPAY_ICON_ATTENTION__ = 'data:image/png;base64,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"style="float: left;padding-left: 10px;';

//Stati della posizione debitoria
const __SMALLPAY_IP_PROCCESSING__ = 'IN ATTIVAZIONE';     //Il primo pagamento non è stato ancora ingaggiato dal cliente.
const __SMALLPAY_IP_ACTIVE__ = 'ATTIVO';             //Il primo pagamento è stato ingaggiato e la transazione si è conclusa con esito positivo.
const __SMALLPAY_IP_NOT_ACTIVE__ = 'NON ATTIVO';         //Il primo pagamento è stato ingaggiato e la transazione si è conclusa con esito negativo.

//Stati della rata
const __SMALLPAY_TS_TO_BE_PAYED__ = 'DA PAGARE';    //Transazione non ancora eseguita
const __SMALLPAY_TS_PAYED__ = 'PAGATO';             //Transazione eseguita con esito positivo
const __SMALLPAY_TS_UNSOLVED__ = 'INSOLUTO';        //Transazione eseguita con esito negativo
const __SMALLPAY_TS_DELETED__ = 'ELIMINATO';        //Rata eliminata. Non verrà eseguito l’addebito
const __SMALLPAY_TS_PROCCESSING__ = 'IN ELABORAZIONE';  //Transazione in corso di elaborazione

const __SMALLPAY_RANGE_1__ = 'range1';
const __SMALLPAY_RANGE_2__ = 'range2';
const __SMALLPAY_RANGE_3__ = 'range3';

const __SMALLPAY_RANGE_KEY_MAP__ = array(
    __SMALLPAY_RANGE_1__ => '_range_1',
    __SMALLPAY_RANGE_2__ => '_range_2',
    __SMALLPAY_RANGE_3__ => '_range_3',
);
