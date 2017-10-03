$(function () {
    $currentAmount = $('.status .currentAmount');
    $movementsAmount = $('.status .movementsAmount');
    $amountAfter = $('.status .amountAfter');
});

function calculate(add, financeMovementId)
{
    $row = $('.movement.' + financeMovementId);
    add = $(add).is(':checked');
    amount = parseFloat($row.find('.amount').text());
    movementsAmount = parseFloat($movementsAmount.text());
    amountAfter = parseFloat($amountAfter.text());

    if (add) {
        movementsAmount += amount;
        amountAfter += amount;
    } else {
        movementsAmount -= amount;
        amountAfter -= amount;
    }

    movementsAmount = Math.round(movementsAmount);
    amountAfter = Math.round(amountAfter);

    $movementsAmount.text(movementsAmount);
    $amountAfter.text(amountAfter);
}