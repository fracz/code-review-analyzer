jQuery ($) ->
  $('#configuration').on 'click', 'button.remove-item', (e) ->
    e.preventDefault()
    $(e.target).parents('.list-group-item').slideUp(-> $(this).remove())
