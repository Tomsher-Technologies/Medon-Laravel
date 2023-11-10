<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Laralink">
    <!-- Site Title -->
    <title></title>
    <!-- <link rel="stylesheet" href="style.css"> -->
</head>
<style>
    *,
    ::after,
    ::before {
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
    }

    html {
        line-height: 1.15;
        -webkit-text-size-adjust: 100%;
    }

    body {
        margin: 0;
    }

    a {
        background-color: transparent;
    }

    b,
    strong {
        font-weight: bolder;
    }

    img {
        border-style: none;
    }

    button,
    input,
    optgroup,
    select,
    textarea {
        font-family: inherit;
        /* 1 */
        font-size: 100%;
        /* 1 */
        line-height: 1.15;
        /* 1 */
        margin: 0;
        /* 2 */
    }

    button,
    input {
        /* 1 */
        overflow: visible;
    }

    button,
    select {
        /* 1 */
        text-transform: none;
    }

    button,
    [type=button],
    [type=reset],
    [type=submit] {
        -webkit-appearance: button;
    }

    body,
    html {
        color: #666;
        font-family: "Inter", sans-serif;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.6em;
        overflow-x: hidden;
        background-color: #f5f6fa;
    }

    p,
    div {
        margin-top: 0;
        line-height: 1.5em;
    }

    p {
        margin-bottom: 15px;
    }

    ul {
        margin: 0 0 25px 0;
        padding-left: 20px;
        list-style: disc;
    }

    img {
        border: 0;
        max-width: 100%;
        height: auto;
        vertical-align: middle;
    }

    a {
        color: inherit;
        text-decoration: none;
        -webkit-transition: all 0.3s ease;
        transition: all 0.3s ease;
    }

    button {
        color: inherit;
        -webkit-transition: all 0.3s ease;
        transition: all 0.3s ease;
    }

    table {
        width: 100%;
        caption-side: bottom;
        border-collapse: collapse;
    }

    th {
        text-align: left;
    }

    td {
        border-top: 1px solid #dbdfea;
    }

    td {
        padding: 10px 15px;
        line-height: 1.55em;
    }

    th {
        padding: 10px 15px;
        line-height: 1.55em;
    }

    b,
    strong {
        font-weight: bold;
    }

    ul {
        padding-left: 15px;
    }

    .tm_f16 {
        font-size: 16px;
    }

    .tm_f50 {
        font-size: 50px;
    }

    .tm_semi_bold {
        font-weight: 600;
    }

    .tm_bold {
        font-weight: 700;
    }

    .tm_m0 {
        margin: 0px;
    }

    .tm_mb2 {
        margin-bottom: 2px;
    }

    .tm_mb5 {
        margin-bottom: 5px;
    }

    .tm_mb10 {
        margin-bottom: 10px;
    }

    .tm_mb20 {
        margin-bottom: 20px;
    }

    .tm_mb30 {
        margin-bottom: 30px;
    }

    .tm_pt0 {
        padding-top: 0;
    }

    .tm_width_1 {
        width: 8.33333333%;
    }

    .tm_width_2 {
        width: 16.66666667%;
    }

    .tm_width_3 {
        width: 25%;
    }

    .tm_width_4 {
        width: 33.33333333%;
    }

    .tm_border_bottom {
        border-bottom: 1px solid #dbdfea;
    }

    .tm_border_top {
        border-top: 1px solid #dbdfea;
    }

    .tm_round_border {
        border: 1px solid #dbdfea;
        overflow: hidden;
        border-radius: 6px;
    }

    .tm_primary_color {
        color: #111;
    }

    .tm_ternary_color {
        color: #b5b5b5;
    }

    .tm_gray_bg {
        background: #f5f6fa;
    }

    .tm_invoice_in {
        position: relative;
        z-index: 100;
    }

    .tm_container {
        /* max-width: 880px; */
        padding: 30px 15px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
    }

    .tm_text_uppercase {
        text-transform: uppercase;
    }

    .tm_text_right {
        text-align: right;
    }

    .tm_align_center {
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
    }

    .tm_border_top_0 {
        border-top: 0;
    }

    .tm_border_none {
        border: none !important;
    }

    .tm_table_responsive {
        overflow-x: auto;
    }

    .tm_table_responsive>table {
        min-width: 600px;
    }

    .tm_invoice {
        background: #fff;
        border-radius: 10px;
        padding: 50px;
    }

    .tm_invoice_footer {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
    }

    .tm_invoice_footer table {
        margin-top: -1px;
    }

    .tm_invoice_footer .tm_left_footer {
        width: 58%;
        padding: 10px 15px;
        -webkit-box-flex: 0;
        -ms-flex: none;
        flex: none;
    }

    .tm_invoice_footer .tm_right_footer {
        width: 42%;
    }

    .tm_invoice.tm_style1 .tm_invoice_right {
        -webkit-box-flex: 0;
        -ms-flex: none;
        flex: none;
        width: 60%;
    }



    .tm_invoice.tm_style1 .tm_invoice_head {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-pack: justify;
        -ms-flex-pack: justify;
        justify-content: space-between;
    }

    .tm_invoice.tm_style1 .tm_invoice_head .tm_invoice_right div {
        line-height: 1em;
    }

    .tm_invoice.tm_style1 .tm_invoice_info {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: justify;
        -ms-flex-pack: justify;
        justify-content: space-between;
    }



    .tm_invoice.tm_style1 .tm_invoice_seperator {
        min-height: 18px;
        border-radius: 1.6em;
        -webkit-box-flex: 1;
        -ms-flex: 1;
        flex: 1;
        margin-right: 20px;
    }

    .tm_invoice.tm_style1 .tm_invoice_info_list {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
    }

    .tm_invoice.tm_style1 .tm_invoice_info_list>*:not(:last-child) {
        margin-right: 20px;
    }

    .tm_invoice.tm_style1 .tm_logo img {
        max-height: 50px;
    }

    .tm_invoice_wrap {
        position: relative;
    }

    .tm_note_list li:not(:last-child) {
        margin-bottom: 5px;
    }

    .tm_padd_15_20 {
        padding: 15px 20px;
    }

    @media (min-width: 1000px) {
        .tm_invoice_btns {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            margin-top: 0px;
            margin-left: 20px;
            position: absolute;
            left: 100%;
            top: 0;
            -webkit-box-shadow: -2px 0 24px -2px rgba(43, 55, 72, 0.05);
            box-shadow: -2px 0 24px -2px rgba(43, 55, 72, 0.05);
            border: 3px solid #fff;
            border-radius: 6px;
            background-color: #fff;
        }

        .tm_invoice_btn {
            display: -webkit-inline-box;
            display: -ms-inline-flexbox;
            display: inline-flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            border: none;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            background-color: transparent;
            position: relative;
        }

        .tm_invoice_btn svg {
            width: 24px;
        }

        .tm_invoice_btn .tm_btn_icon {
            padding: 0;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            height: 42px;
            width: 42px;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
        }

        .tm_invoice_btn .tm_btn_text {
            position: absolute;
            left: 100%;
            background-color: #111;
            color: #fff;
            padding: 3px 12px;
            display: inline-block;
            margin-left: 10px;
            border-radius: 5px;
            top: 50%;
            -webkit-transform: translateY(-50%);
            transform: translateY(-50%);
            font-weight: 500;
            min-height: 28px;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
        }

        .tm_invoice_btn .tm_btn_text:before {
            content: "";
            height: 10px;
            width: 10px;
            position: absolute;
            background-color: #111;
            -webkit-transform: rotate(45deg);
            transform: rotate(45deg);
            left: -3px;
            top: 50%;
            margin-top: -6px;
            border-radius: 2px;
        }
    }

    .tm_invoice_btn:not(:last-child) {
        margin-bottom: 3px;
    }

    .tm_invoice_btn.tm_color1 {
        background-color: rgba(0, 122, 255, 0.1);
        color: #007aff;
        border-radius: 5px 5px 0 0;
    }

    .tm_invoice_btn.tm_color2 {
        background-color: rgba(52, 199, 89, 0.1);
        color: #34c759;
        border-radius: 0 0 5px 5px;
    }
</style>

<body>
    <div class="tm_container">
        <div class="tm_invoice_wrap">
            <div class="tm_invoice tm_style1" id="tm_download_section">
                <div class="tm_invoice_in">
                    <div class="tm_invoice_head tm_align_center tm_mb20">
                        <div class="tm_invoice_left">
                            <div class="tm_logo"><img src="assets/img/logo.svg" alt="Logo"></div>
                        </div>
                        <div class="tm_invoice_right tm_text_right">
                            <div class="tm_primary_color tm_f50 tm_text_uppercase">Invoice</div>
                        </div>
                    </div>
                    <div class="tm_invoice_info tm_mb20">
                        <div class="tm_invoice_seperator tm_gray_bg"></div>
                        <div class="tm_invoice_info_list">
                            <p class="tm_invoice_number tm_m0">Invoice No: <b class="tm_primary_color">#LL93784</b></p>
                            <p class="tm_invoice_date tm_m0">Date: <b class="tm_primary_color">01.07.2022</b></p>
                        </div>
                    </div>
                    <div class="tm_invoice_head tm_mb10">
                        <div class="tm_invoice_left">
                            <p class="tm_mb2"><b class="tm_primary_color">Invoice To:</b></p>
                            <p>
                                Lowell H. Dominguez <br>
                                84 Spilman Street, London <br>United Kingdom <br>
                                lowell@gmail.com
                            </p>
                        </div>
                        <div class="tm_invoice_right tm_text_right">
                            <p class="tm_mb2"><b class="tm_primary_color">Pay To:</b></p>
                            <p>
                                Laralink Ltd <br>
                                86-90 Paul Street, London<br>
                                England EC2A 4NE <br>
                                demo@gmail.com
                            </p>
                        </div>
                    </div>
                    <div class="tm_table tm_style1 tm_mb30">
                        <div class="tm_round_border">
                            <div class="tm_table_responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="tm_width_3 tm_semi_bold tm_primary_color tm_gray_bg">Item</th>
                                            <th class="tm_width_4 tm_semi_bold tm_primary_color tm_gray_bg">Description
                                            </th>
                                            <th class="tm_width_2 tm_semi_bold tm_primary_color tm_gray_bg">Price</th>
                                            <th class="tm_width_1 tm_semi_bold tm_primary_color tm_gray_bg">Qty</th>
                                            <th
                                                class="tm_width_2 tm_semi_bold tm_primary_color tm_gray_bg tm_text_right">
                                                Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="tm_width_3">1. Website Design</td>
                                            <td class="tm_width_4">Six web page designs and three times revision</td>
                                            <td class="tm_width_2">$350</td>
                                            <td class="tm_width_1">1</td>
                                            <td class="tm_width_2 tm_text_right">$350</td>
                                        </tr>
                                        <tr>
                                            <td class="tm_width_3">2. Web Development</td>
                                            <td class="tm_width_4">Convert pixel-perfect frontend and make it dynamic
                                            </td>
                                            <td class="tm_width_2">$600</td>
                                            <td class="tm_width_1">1</td>
                                            <td class="tm_width_2 tm_text_right">$600</td>
                                        </tr>
                                        <tr>
                                            <td class="tm_width_3">3. App Development</td>
                                            <td class="tm_width_4">Android & Ios Application Development</td>
                                            <td class="tm_width_2">$200</td>
                                            <td class="tm_width_1">2</td>
                                            <td class="tm_width_2 tm_text_right">$400</td>
                                        </tr>
                                        <tr>
                                            <td class="tm_width_3">4. Digital Marketing</td>
                                            <td class="tm_width_4">Facebook, Youtube and Google Marketing</td>
                                            <td class="tm_width_2">$100</td>
                                            <td class="tm_width_1">3</td>
                                            <td class="tm_width_2 tm_text_right">$300</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tm_invoice_footer">
                            <div class="tm_left_footer">
                                <p class="tm_mb2"><b class="tm_primary_color">Payment info:</b></p>
                                <p class="tm_m0">Credit Card - 236***********928 <br>Amount: $1732</p>
                            </div>
                            <div class="tm_right_footer">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="tm_width_3 tm_primary_color tm_border_none tm_bold">Subtoal</td>
                                            <td
                                                class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_bold">
                                                $1650</td>
                                        </tr>
                                        <tr>
                                            <td class="tm_width_3 tm_primary_color tm_border_none tm_pt0">Tax <span
                                                    class="tm_ternary_color">(5%)</span></td>
                                            <td class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_pt0">
                                                +$82</td>
                                        </tr>
                                        <tr class="tm_border_top tm_border_bottom">
                                            <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_primary_color">Grand
                                                Total </td>
                                            <td
                                                class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_primary_color tm_text_right">
                                                $1732</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tm_padd_15_20 tm_round_border">
                        <p class="tm_mb5"><b class="tm_primary_color">Terms & Conditions:</b></p>
                        <ul class="tm_m0 tm_note_list">
                            <li>All claims relating to quantity or shipping errors shall be waived by Buyer unless made
                                in writing to Seller within thirty (30) days after delivery of goods to the address
                                stated.</li>
                            <li>Delivery dates are not guaranteed and Seller has no liability for damages that may be
                                incurred due to any delay in shipment of goods hereunder. Taxes are excluded unless
                                otherwise stated.</li>
                        </ul>
                    </div><!-- .tm_note -->
                </div>
            </div>
            <div class="tm_invoice_btns tm_hide_print">
                <a href="javascript:window.print()" class="tm_invoice_btn tm_color1">
                    <span class="tm_btn_icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                            <path
                                d="M384 368h24a40.12 40.12 0 0040-40V168a40.12 40.12 0 00-40-40H104a40.12 40.12 0 00-40 40v160a40.12 40.12 0 0040 40h24"
                                fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" />
                            <rect x="128" y="240" width="256" height="208" rx="24.32" ry="24.32"
                                fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" />
                            <path d="M384 128v-24a40.12 40.12 0 00-40-40H168a40.12 40.12 0 00-40 40v24" fill="none"
                                stroke="currentColor" stroke-linejoin="round" stroke-width="32" />
                            <circle cx="392" cy="184" r="24" fill='currentColor' />
                        </svg>
                    </span>
                    <span class="tm_btn_text">Print</span>
                </a>
                <button id="tm_download_btn" class="tm_invoice_btn tm_color2">
                    <span class="tm_btn_icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                            <path
                                d="M320 336h76c55 0 100-21.21 100-75.6s-53-73.47-96-75.6C391.11 99.74 329 48 256 48c-69 0-113.44 45.79-128 91.2-60 5.7-112 35.88-112 98.4S70 336 136 336h56M192 400.1l64 63.9 64-63.9M256 224v224.03"
                                fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="32" />
                        </svg>
                    </span>
                    <span class="tm_btn_text">Download</span>
                </button>
            </div>
        </div>
    </div>
    <!-- <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/jspdf.min.js"></script>
  <script src="assets/js/html2canvas.min.js"></script>
  <script src="assets/js/main.js"></script> -->
</body>

</html>
