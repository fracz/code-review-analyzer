jQuery ($) ->
  $type = $('#type')
  $type.on 'change', (e) ->
    $element = $(e.target)
    if $element.val() == 'stash'
      $('div.repository').slideDown()
    else
      $('div.repository').slideUp()

  $type.change()
