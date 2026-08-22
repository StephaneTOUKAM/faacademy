<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture</title>
    <style>
        html{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        h1{
            color: rgb(224, 224, 224);
            font-weight: 400;
            font-size: 40px;
        }

        h2{
            font-size: 16px;
        }

        .entete{
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid #007fc5;
            margin-bottom: 35px;
        }

        .color-text{
            color: #007fc5;
        }

        .bold-text{
            font-weight: 700;
        }

        .first-p{
            font-size: 12px;
            padding: 0px 12px;
            display: block;
        }

        .entete img{
            width: 130px;
        }

        .entete-corps{
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 0px 12px;
        }

        .surligne span{
            background-color: green;
        }

        .corps-entete-droit strong{
            display: inline-block;
            width: 170px;
            text-align: right;
            margin-right: 15px;
            padding-top: 1px;
            padding-bottom: 1px;
        }
        .droit{
            text-align: right;
        }
        .corps-entete-gauche{
            padding-top: 14px;
            line-height: 15px;
        }
        .contraint-box{
            padding: 0px 12px;
        }

        .table-faq{
            width: 100%;
            text-align: right;
            border: 0;
            margin-top: 8px;
            margin-bottom: 15px;
            /* border: 1px solid #000; */
        }
        .table-faq td, .table-faq th{
            padding: 7px 5px;
        }
        .table-faq tr th:nth-of-type(2), .table-faq tbody tr td:nth-of-type(2){
            text-align: left;
            max-width: 60%;
        }
        .table-faq tr th{
            background-color: rgb(185, 185, 185);
        }
        .table-faq th:nth-child(1),
        .table-faq th:nth-child(2),
        .table-faq th:nth-child(3){
            background: #007fc5;
            color: #FFF;
        }

        .table-faq tbody tr td{
            border-bottom: 1px solid lightgray;
        }
        .table-faq tbody tr:last-of-type td{
            border-bottom: 2px solid #000;
        }
        tfoot{
            font-weight: 600;
        }
        tfoot tr:last-of-type td:nth-last-child(1), 
        tfoot tr:last-of-type td:nth-last-child(2){
            background: #007fc5;
            color: #FFF;
        }

        .space-p{
            margin-top: 35px;
        }

        .signature{
            max-width: 100px;
            max-height: 100px;
            display: block;
        }

        .container-infos{
            display: flex;
            justify-content: space-evenly;
            position: relative;
            z-index: 10;
        }

        .container-infos p{
            margin: 0;
        }

        .container-info{
            text-align: center;
            display: flex;
            align-items: center;
            flex-direction: column;
            margin-top: 15px;
            position: relative;
        }

        .container-info img{
            width: 20px;
            height: 20px;
        }

        .container-img{
            width: 30px;
            height: 30px;
            padding: 5px;
            border: 1px solid rgb(95, 95, 95);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-bottom: 6px;
            background-color: #FFF;
        }

        .last-p{
            position: relative;
        }

        .last-p::after{
            content: '';
            width: 100%;
            height: 3px;
            background-color: #007fc5;
            position: absolute;
            top: -72px;
            left: 0;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="container-principal">
        <div class="entete">
            <h1>RECHNUNG</h1>
            <img src="F@Academy_logo_symb_rgb_2000px.png" alt="logo">
        </div>
        <div class="corps">
            <span class="color-text bold-text first-p">F@Academy, Lettenweg 13, 79618 Rheinfelden</span>
            <div class="entete-corps">
                <div class="corps-entete-gauche">
                    Client x <br>
                    Straßenname 345 <br>
                    PLZ Ort <br>
                    Land (optional) <br>
                </div>
                <div class="corps-entete-droit">
                    <strong>Rechnung Nr.:</strong>1001<br>
                    <strong>Rechnungsdatum:</strong>04.08.2021<br>
                    <strong>Lieferdatum:</strong>01.08.2021<br>
                    <div class="surligne"><strong> <span>Kundennr.:</span></strong><span>4227</span></div>
                    <strong>Ansprechpartner:</strong>Paul, Fansi<br>
                </div>
            </div>
            <div class="container-data droit">
                <span>17.08.2018</span>
            </div>
        </div>
        <div class="contraint-box">
            <h2 class="color-text">RECHNUNG NR. 1001</h2>
            <span class="dear-text">Sehr geehrte Damen und Herren,</span>
            <p>Vielen Dank für Ihr Vertrauen in die <strong>F@Academy</strong>. Wir stellen Ihnen hiermit folgende Leistungen in Rechnung:</p>
        </div>
        <table class="table-faq" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Pos.</th>
                    <th>Beschreibung</th>
                    <th>Menge</th>
                    <th>Stückpreis</th>
                    <th>Gesamtpreis</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1.</td>
                    <td>Grundlagen der Elektrotechnik</td>
                    <td>X2</td>
                    <td>5 €</td>
                    <td>10 €</td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>Lineare Funktionen</td>
                    <td>X3</td>
                    <td>3 €</td>
                    <td>15 €</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">Summe Netto</td>
                    <td>25 €</td>
                </tr>
                <tr>
                    <td colspan="3"></td>
                    <td>Gesamtsumme</td>
                    <td>25 €</td>
                </tr>
            </tfoot>
        </table>
        <small>Zahlungsbedingungen: Zahlung innerhalb von 14 Tagen ab Rechnungseingang ohne Abzüge.</small>
        <p class="space-p">Bei Rückfragen stehen wir selbstverständlich jederzeit gerne zur Verfügung.</p>
        <span>Mit freundlichen Grüßen</span>
        <img class="signature" src="F@Academy_logo_symb_rgb_2000px.png" alt="signature">
        <div class="footer">
            <div class="container-infos">
                <div class="container-info">
                    <div class="container-img">
                        <img src="F@Academy_logo_symb_rgb_2000px.png" alt="">
                    </div>
                    <p>
                        F@Academy <br>
                        Lettenweg 13 <br>
                        79618 Rheifelde <br>
                    </p>
                </div>
                <div class="container-info">
                    <div class="container-img">
                        <img src="F@Academy_logo_symb_rgb_2000px.png" alt="">
                    </div>
                    <p>
                        +4917624537308 <br>
                        https://faacademy.de <br>
                        info@faacademy.de <br>
                    </p>
                </div>
                <div class="container-info">
                    <div class="container-img">
                        <img src="F@Academy_logo_symb_rgb_2000px.png" alt="">
                    </div>
                    <p>
                        TARGOBANK <br>
                        DE45300209005360594849 <br>
                        BIC: CMCIDEDDxxx <br>
                    </p>
                </div>
                <div class="container-info">
                    <div class="container-img">
                        <img src="F@Academy_logo_symb_rgb_2000px.png" alt="">
                    </div>
                    <p>
                        Geschäftsführer: <br>
                        Paul Fansi <br>
                    </p>
                </div>
            </div>
            <p class="last-p" style="text-align: center; margin: 0; margin-top: 2px;">Hinweis : Kein Umsatzsteuerausweis nach §19 UStG</p>
        </div>
    </div>
</body>
</html>