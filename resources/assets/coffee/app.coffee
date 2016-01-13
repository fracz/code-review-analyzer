jQuery ($) ->
  $('.selectpicker').selectpicker()
  $from = $('input[type=text][name=from]')
  $to = $('input[type=text][name=to]')
  if $from and $to
    $from.datetimepicker(
      format: 'DD-MM-YYYY'
      sideBySide: false
    )
    $from.on 'dp.change', (e) ->
      $to.data('DateTimePicker').minDate(e.date)
    $to.datetimepicker(
      format: 'DD-MM-YYYY'
      sideBySide: false
    )
    $to.on 'dp.change', (e) ->
      $from.data('DateTimePicker').maxDate(e.date)
