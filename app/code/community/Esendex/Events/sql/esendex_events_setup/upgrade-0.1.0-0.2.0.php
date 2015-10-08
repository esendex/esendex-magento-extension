<?php
/**
 * Copyright (C) 2015 Esendex Ltd.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the Esendex Community License v1.0 as published by
 * the Esendex Ltd.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * Esendex Community Licence v1.0 for more details.
 *
 * You should have received a copy of the Esendex Community Licence v1.0
 * along with this program.  If not, see <http://www.esendex.com/esendexcommunitylicence/>
 */

/** @var $this Esendex_Sms_Model_Resource_Setup */
$this->startSetup();

// Remove all sample messages
$this->getConnection()->query('TRUNCATE TABLE ' . $this->getTable('esendex_sms/event_sample_message_template'));

// Notifications Sample Messages

// Order Shipped
$this->addSampleMessage(
    'Order Shipped',
    'en_GB',
    'Great news, $FIRSTNAME$! Your $STORENAME$ order #$ORDERNO$ is on its way. Thank you for shopping with $STORENAME$.'
);

$this->addSampleMessage(
    'Order Shipped',
    'fr_FR',
    'Excellente nouvelle, $FIRSTNAME$ ! Votre commande $STORENAME$ #$ORDERNO$ est en cours d\'acheminement. Merci pour votre achat chez $STORENAME$.'
);

$this->addSampleMessage(
    'Order Shipped',
    'es_ES',
    'Hola $FIRSTNAME$, tu $STORENAME$ pedido #$ORDERNO$ está en camino. Gracias por comprar en $STORENAME$.'
);

$this->addSampleMessage(
    'Order Shipped',
    'de_DE',
    'Gute Nachrichten $FIRSTNAME$! Ihre $STORENAME$ Bestellung #$ORDERNO$ ist unterwegs. Vielen Dank, dass Sie bei $STORENAME$ eingekauft haben.'
);

// Order Shipped with Tracking
$this->addSampleMessage(
    'Order Shipped with Tracking',
    'en_GB',
    'Great news, $FIRSTNAME$! Your $STORENAME$ order #$ORDERNO$ is on its way with $PROVIDER$. To track your delivery please use your tracking code $TRACKINGNO$.'
);

$this->addSampleMessage(
    'Order Shipped with Tracking',
    'es_ES',
    'Hola $FIRSTNAME$, tu $STORENAME$ pedido #$ORDERNO$ está en camino con $PROVIDER$. Para seguir el progreso del pedido puedes usar este código $TRACKINGNO$.'
);

$this->addSampleMessage(
    'Order Shipped with Tracking',
    'fr_FR',
    'Excellente nouvelle, $FIRSTNAME$ ! Votre commande $STORENAME$ #$ORDERNO$ est en cours d\'acheminement avec $PROVIDER$. Pour suivre votre colis, veuillez utliser le numéro de suivi $TRACKINGNO$.'
);


$this->addSampleMessage(
    'Order Shipped with Tracking',
    'de_DE',
    'Gute Nachrichten $FIRSTNAME$! Ihre $STORENAME$ Bestellung #$ORDERNO$ ist unterwegs. Sie können Ihre Bestellung unter dem Code $TRACKINGNO$ nachverfolgen.'
);


// Order Status Changed - Processing
$this->addSampleMessage(
    'Order Status Changed - Processing',
    'en_GB',
    'Hi $FIRSTNAME$, your $STORENAME$ order #$ORDERNO$ for $PRODUCT$ has been received. To check the status of your order please visit $ORDERURL$. Thank you for shopping with $STORENAME$.'
);

$this->addSampleMessage(
    'Order Status Changed - Processing',
    'es_ES',
    'Hola $FIRSTNAME$, tu $STORENAME$ pedido #$ORDERNO$ para $PRODUCT$ se ha registrado. Para saber el estado del pedido visita $ORDERURL$. Gracias por comprar en $STORENAME$.'
);

$this->addSampleMessage(
    'Order Status Changed - Processing',
    'fr_FR',
    'Bonjour $FIRSTNAME$, nous avons reçu votre commande $STORENAME$ #$ORDERNO$ pour $PRODUCT$. Pour vérifier le statut de votre commande, veuillez vous rendre sur $ORDERURL$. Merci pour votre achat chez $STORENAME$.'
);

$this->addSampleMessage(
    'Order Status Changed - Processing',
    'de_DE',
    'Hallo $FIRSTNAME$! Wir haben Ihre $STORENAME$ Bestellung #$ORDERNO$ von $PRODUCT$ erhalten. Um den Status Ihrer Bestellung zu überprüfen, besuchen Sie bitte $ORDERURL$. Vielen Dank, dass Sie bei $STORENAME$ eingekauft haben!'
);

// Order Status Changed - Pending Payment
$this->addSampleMessage(
    'Order Status Changed - Pending Payment',
    'en_GB',
    'Hi $FIRSTNAME$, the payment for your $STORENAME$ order #$ORDERNO$ is being processed. To check the status of your payment please visit $ORDERURL$ or call $STORECONTACTTELEPHONE$ for further information.'
);

$this->addSampleMessage(
    'Order Status Changed - Pending Payment',
    'es_ES',
    'Hola $FIRSTNAME$, el pago a $STORENAME$ pedido #$ORDERNO$ está siendo procesado. Para comprobar el estado de tu pedido visita $ORDERURL$ o llama al $STORECONTACTTELEPHONE$'
);

$this->addSampleMessage(
    'Order Status Changed - Pending Payment',
    'fr_FR',
    'Bonjour $FIRSTNAME$, le paiement pour votre commande $STORENAME$ #$ORDERNO$ est en cours de traitement. Pour vérifier le statut de votre paiement, veuillez vous rendre sur $ORDERURL$ ou appeler le $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Pending Payment',
    'de_DE',
    'Hallo $FIRSTNAME$! Die Zahlung für Ihre $STORENAME$ Bestellung #$ORDERNO$ wird gerade bearbeitet. Um den Status Ihrer Bestellung zu überprüfen, besuchen Sie bitte $ORDERURL$ oder wählen Sie $STORECONTACTTELEPHONE$ für weitere Informationen.'
);

// Order Status Changed - Suspected Fraud
$this->addSampleMessage(
    'Order Status Changed - Suspected Fraud',
    'en_GB',
    'Hi $FIRSTNAME$, thank you for your recent $STORENAME$ order #$ORDERNO$. To complete your order we\'ll need to speak to you, please call us on $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Suspected Fraud',
    'es_ES',
    'Hola $FIRSTNAME$, gracias por tu compra en $STORENAME$ pedido #$ORDERNO$. Para completar el pedido tenemos que hablar contigo, por favor llama al $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Suspected Fraud',
    'fr_FR',
    'Bonjour $FIRSTNAME$, merci pour votre récente commande $STORENAME$ #$ORDERNO$. Afin de traiter votre commande, il nous faut vous contacter, veuillez nous appeler au $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Suspected Fraud',
    'de_DE',
    'Hallo $FIRSTNAME$! Vielen Dank für Ihre kürzlich getätigte $STORENAME$ Bestellung #$ORDERNO$. Um Ihre Bestellung abzuschließen, müssen wir persönlich mit Ihnen sprechen. Bitte rufen Sie uns an unter $STORECONTACTTELEPHONE$.'
);

// Order Status Changed - Payment Review
$this->addSampleMessage(
    'Order Status Changed - Payment Review',
    'en_GB',
    'Hi $FIRSTNAME$, the payment for your $STORENAME$ order #$ORDERNO$ is being processed. To check the status of your payment please visit $ORDERURL$ or call $STORECONTACTTELEPHONE$ for further information.'
);

$this->addSampleMessage(
    'Order Status Changed - Payment Review',
    'es_ES',
    'Hola $FIRSTNAME$, el pago de tu $STORENAME$ pedido #$ORDERNO$ se esta procesando. Para comprobar el estado de la compra visita $ORDERURL$ o llama al $STORECONTACTTELEPHONE$'
);

$this->addSampleMessage(
    'Order Status Changed - Payment Review',
    'fr_FR',
    'Bonjour $FIRSTNAME$, le paiement pour votre commande $STORENAME$ #$ORDERNO$ est en cours de traitement. Pour vérifier le statut de votre paiement, veuillez vous rendre sur $ORDERURL$ ou appeler le $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Payment Review',
    'de_DE',
    'Hallo $FIRSTNAME$! Die Zahlung für Ihre $STORENAME$ Bestellung #$ORDERNO$ wird gerade bearbeitet. Um den Status Ihrer Zahlung zu überprüfen, besuchen Sie bitte $ORDERURL$ oder wählen Sie $STORECONTACTTELEPHONE$ für weitere Informationen. '
);

// Order Status Changed - Pending
$this->addSampleMessage(
    'Order Status Changed - Pending',
    'en_GB',
    'Hi $FIRSTNAME$, your $STORENAME$ order #$ORDERNO$ is now being processed. To check the status of your order please visit $ORDERURL$ or call $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Pending',
    'es_ES',
    'Hola $FIRSTNAME$, tu $STORENAME$ pedido #$ORDERNO$ está siendo procesado. Para ver el estado del pedido visita $ORDERURL$ o llama al $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Pending',
    'fr_FR',
    'Bonjour $FIRSTNAME$, votre commande $STORENAME$ #$ORDERNO$ est maintenant en cours de traitement. Pour vérifier le statut de votre commande, veuillez vous rendre sur $ORDERURL$ ou appeler le $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Pending',
    'de_DE',
    'Hallo $FIRSTNAME$! Ihre $STORENAME$ Bestellung #$ORDERNO$ wird gerade bearbeitet. Um den Status Ihrer Bestellung zu überprüfen, besuchen Sie bitte $ORDERURL$ oder wählen Sie $STORECONTACTTELEPHONE$.'
);

// Order Status Changed - On Hold
$this->addSampleMessage(
    'Order Status Changed - On Hold',
    'en_GB',
    'Hi $FIRSTNAME$, your $STORENAME$ order #$ORDERNO$ has been placed on hold. This may mean an item you ordered is temporarily out of stock. To check the status of your order please visit $ORDERURL$ or call $STORECONTACTTELEPHONE$ for further information.'
);

$this->addSampleMessage(
    'Order Status Changed - On Hold',
    'es_ES',
    'Hola $FIRSTNAME$, tu $STORENAME$ pedido #$ORDERNO$ está en espera. Esto puede ser debido a una falta de stok. Rogamos visites $ORDERURL$ o llames al $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - On Hold',
    'fr_FR',
    'Bonjour $FIRSTNAME$, votre commande $STORENAME$ #$ORDERNO$ a été suspendue. Il se peut qu\'un article que vous avez commandé soit temporairement indisponible. Pour vérifier le statut de votre commande, veuillez vous rendre sur $ORDERURL$ ou appeler le $STORECONTACTTELEPHONE$ pour plus d\'informations.'
);

$this->addSampleMessage(
    'Order Status Changed - On Hold',
    'de_DE',
    'Hallo $FIRSTNAME$! Ihre $STORENAME$ Bestellung #$ORDERNO$ wurde. Wenn Sie Ihre Bestellung abschließen möchten, wählen Sie folgende Nummer $STORECONTACTTELEPHONE$ oder besuchen Sie $ORDERURL$.'
);

// Order Status Changed - Complete
$this->addSampleMessage(
    'Order Status Changed - Complete',
    'en_GB',
    'Great news, $FIRSTNAME$! Your $STORENAME$ order #$ORDERNO$ has been shipped today. Thank you for shopping with $STORENAME$.'
);

$this->addSampleMessage(
    'Order Status Changed - Complete',
    'es_ES',
    'Hola $FIRSTNAME$, tu $STORENAME$ pedido #$ORDERNO$ ha sido enviado hoy. Gracias por comprar en $STORENAME$.'
);

$this->addSampleMessage(
    'Order Status Changed - Complete',
    'fr_FR',
    'Excellente nouvelle, $FIRSTNAME$ ! Votre commande $STORENAME$ #$ORDERNO$ a été expédiée aujourd\'hui. Merci pour votre achat chez $STORENAME$.'
);

$this->addSampleMessage(
    'Order Status Changed - Complete',
    'de_DE',
    'Gute Nachrichten $FIRSTNAME$! Ihre $STORENAME$ Bestellung #$ORDERNO$ hat das Lager verlassen. Vielen Dank, dass Sie bei $STORENAME$ eingekauft haben. '
);

// Order Status Changed - Closed
$this->addSampleMessage(
    'Order Status Changed - Closed',
    'en_GB',
    'Hi $FIRSTNAME$, we received your $STORENAME$ order #$ORDERNO$ today. You should receive your refund in the next few days. For further information please call $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Closed',
    'es_ES',
    'Hola $FIRSTNAME$, hemos recibido tu $STORENAME$ pedido #$ORDERNO$ hoy. Te devolveremos el dinero en los próximos días. Para más información llama al $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Closed',
    'fr_FR',
    'Bonjour $FIRSTNAME$, nous avons reçu votre commande $STORENAME$ #$ORDERNO$ aujourd\'hui. Vous devriez être remboursé(e) dans les prochains jours. Pour plus d\'informations, veuillez appeler le $STORECONTACTTELEPHONE$.'
);

$this->addSampleMessage(
    'Order Status Changed - Closed',
    'de_DE',
    'Hallo $FIRSTNAME$! Wir haben Ihren $STORENAME$ #$ORDERNO$ Auftrag heute erhalten. Wir werden Ihnen in den nächsten Tagen Ihr Geld rückerstatten. Für weitere Informationen kontaktieren Sie uns bitte unter $STORECONTACTTELEPHONE$'
);

// Order Status Changed - Canceled
$this->addSampleMessage(
    'Order Status Changed - Canceled',
    'en_GB',
    'Hi $FIRSTNAME$, your $STORENAME$ order #$ORDERNO$ has been cancelled. If you would like to complete your order please call $STORECONTACTTELEPHONE$ or visit $ORDERURL$.'
);

$this->addSampleMessage(
    'Order Status Changed - Canceled',
    'es_ES',
    'Hola $FIRSTNAME$, tu $STORENAME$ pedido #$ORDERNO$ ha sido cancelado. Si sigues queriendo completar tu pedido llama al $STORECONTACTTELEPHONE$ o visita $ORDERURL$.'
);

$this->addSampleMessage(
    'Order Status Changed - Canceled',
    'fr_FR',
    'Bonjour $FIRSTNAME$, votre commande $STORENAME$ #$ORDERNO$ a été annulée. Si vous souhaitez terminer votre commande, veuillez appeler le $STORECONTACTTELEPHONE$ ou vous rendre sur $ORDERURL$.'
);

$this->addSampleMessage(
    'Order Status Changed - Canceled',
    'de_DE',
    'Hallo $FIRSTNAME$! Ihre $ShopName$ Bestellung wurde storniert. Wenn Sie Ihre Bestellung abschließen möchten, wählen Sie folgende Nummer $STORECONTACTTELEPHONE$ oder besuchen Sie $ORDERURL$.'
);


// Admin Sales Report Sample Messages
$GBmessage = <<<'GBMESSAGE'
Sales for $STORENAME$ from $STARTDATE$ to $ENDDATE$ were:
Net total - $NETTOTAL$
Grand total - $GRANDTOTAL$
Orders - $NUMBEROFORDERS$
Items - $NUMBEROFITEMSSOLD$
GBMESSAGE;

$FRmessage = <<<'FRMESSAGE'
Les ventes pour $STORENAME$ du $STARTDATE$ au $ENDDATE$ sont :
Total net - $NETTOTAL$
Grand total - $GRANDTOTAL$
Commandes - $NUMBERORORDERS$
Articles - $NUMBEROFITEMSSOLD$
FRMESSAGE;

$ESmessage = <<<'ESMESSAGE'
Ventas para $STORENAME$ desde $STARTDATE$ a $ENDDATE$ fueron:
Total neto - $NETTOTAL$
Total - $GRANDTOTAL$
Pedidos - $NUMBEROFORDERS$
Articulos - $NUMBEROFITEMSSOLD$
ESMESSAGE;

$DEmessage = <<<'DEMESSAGE'
Verkäufe für $STORENAME$ von $STARTDATE$ bis $ENDDATE$:
Nettogesamtbetrag - $NETTOTAL$
Gesamtbetrag - $GRANDTOTAL$
Bestellungen - $NUMBEROFORDERS$
Artikel - $NUMBEROFITEMSSOLD$
DEMESSAGE;

$this->addSampleMessage('Admin Sales Report', 'en_GB', $GBmessage);
$this->addSampleMessage('Admin Sales Report', 'fr_FR', $FRmessage);
$this->addSampleMessage('Admin Sales Report', 'es_ES', $ESmessage);
$this->addSampleMessage('Admin Sales Report', 'de_DE', $DEmessage);

$this->endSetup();
