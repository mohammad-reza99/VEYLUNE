SELECT
    o.order_number,
    LOWER(HEX(o.id)) AS order_id,
    os.technical_name AS order_state,
    ts.technical_name AS payment_state,
    o.created_at
FROM `order` o
INNER JOIN state_machine_state os ON os.id = o.state_id
INNER JOIN order_transaction ot
    ON ot.order_id = o.id
    AND ot.order_version_id = o.version_id
INNER JOIN state_machine_state ts ON ts.id = ot.state_id
WHERE o.sales_channel_id = UNHEX('019f7a00000070008000000000000001')
ORDER BY o.created_at DESC
LIMIT 8;

SELECT
    COUNT(*) AS latest_order_sand_lines,
    o.order_number
FROM `order` o
INNER JOIN order_line_item li ON li.order_id = o.id AND li.order_version_id = o.version_id
WHERE o.sales_channel_id = UNHEX('019f7a00000070008000000000000001')
    AND li.product_id = UNHEX('a4852151befb0b5fd6c42615a6fa64a2')
    AND o.id = (
        SELECT newest.id FROM `order` newest
        WHERE newest.sales_channel_id = UNHEX('019f7a00000070008000000000000001')
        ORDER BY newest.created_at DESC LIMIT 1
    )
GROUP BY o.order_number;

SELECT
    product_number,
    stock,
    available_stock,
    is_closeout
FROM product
WHERE product_number IN ('VLT-TEST-V-SAND', 'VLT-TEST-V-CHARCOAL')
    AND version_id = UNHEX('0fa91ce3e96a4bc2be4bd9ce752c3425');

SELECT
    o.order_number,
    ts.technical_name AS payment_state,
    rs.technical_name AS refund_state,
    r.reason,
    r.amount
FROM order_transaction_capture_refund r
INNER JOIN state_machine_state rs ON rs.id = r.state_id
INNER JOIN order_transaction_capture c ON c.id = r.capture_id
INNER JOIN order_transaction t
    ON t.id = c.order_transaction_id
    AND t.version_id = c.order_transaction_version_id
INNER JOIN state_machine_state ts ON ts.id = t.state_id
INNER JOIN `order` o ON o.id = t.order_id AND o.version_id = t.order_version_id
WHERE o.sales_channel_id = UNHEX('019f7a00000070008000000000000001')
ORDER BY r.created_at DESC
LIMIT 8;
