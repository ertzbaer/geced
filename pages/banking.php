<?php
/**
 * Banking Seite
 */
?>

<div class="geex-content__header">
    <div class="geex-content__header__content">
        <h2 class="geex-content__header__title">Banking</h2>
        <p class="geex-content__header__subtitle">Finanzübersicht</p>
    </div>
</div>

<div class="geex-content__wrapper">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Gesamtguthaben</h6>
                    <h2 class="text-white">12.450,00 €</h2>
                    <p class="mb-0"><i class="uil uil-arrow-up"></i> +5.2% seit letztem Monat</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Einnahmen</h6>
                    <h2 class="text-white">8.230,00 €</h2>
                    <p class="mb-0"><i class="uil uil-arrow-up"></i> +12.5% seit letztem Monat</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Ausgaben</h6>
                    <h2 class="text-white">3.890,00 €</h2>
                    <p class="mb-0"><i class="uil uil-arrow-down"></i> -3.2% seit letztem Monat</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Letzte Transaktionen</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Beschreibung</th>
                                    <th>Kategorie</th>
                                    <th class="text-end">Betrag</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>15.08.2024</td>
                                    <td>Gehaltszahlung</td>
                                    <td><span class="badge badge-success">Einnahme</span></td>
                                    <td class="text-end text-success">+3.500,00 €</td>
                                </tr>
                                <tr>
                                    <td>14.08.2024</td>
                                    <td>Supermarkt</td>
                                    <td><span class="badge badge-danger">Ausgabe</span></td>
                                    <td class="text-end text-danger">-125,50 €</td>
                                </tr>
                                <tr>
                                    <td>12.08.2024</td>
                                    <td>Online-Verkauf</td>
                                    <td><span class="badge badge-success">Einnahme</span></td>
                                    <td class="text-end text-success">+450,00 €</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>