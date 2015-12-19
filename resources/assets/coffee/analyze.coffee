jQuery ($) ->
  token = $('meta[name=_token]').attr('content')

  $('button.overall-rank').on 'click', (e) ->
    e.preventDefault()
    $('p.overall-ranking').slideToggle()

  $('button.hide-code').on 'click', (e) ->
    e.preventDefault()
    $target = $(e.target)
    $target.parent().siblings('pre').slideUp()
    $target.hide()
    $target.siblings('.fetch-code').show()

  $('button.fetch-code').on 'click', (e) ->
    e.preventDefault()
    $target = $(e.target)
    $hide = $target.siblings('.hide-code')
    $code = $target.parent().siblings('pre')

    if $code.length == 0
      project = $target.closest('ul.result-list').data('project')

      $.ajax
        url: "/projects/#{project}/code"
        data:
          change: $target.data('change')
          revision: $target.data('revision')
          filename: $target.data('file')
          _token: token
        type: 'post'
        dataType: 'json'
        error: ->
          alert('Wystąpił błąd.')
        success: (response) ->
          $container = $(document.createElement('pre'))
          $code = $(document.createElement('code'))
            .addClass("language-#{response.language}")
            .addClass('line-numbers')
          if ($target.data('line'))
            $container.attr('data-line', 10)
            $container.attr('data-start', $target.data('line') - 10)
          $container.append($code)
          code = []
          for part in response.code.parts
            code = code.concat(part.code)
          code = code.slice($target.data('line') - 10, $target.data('line') + 10)
          $code.html(code.join('\n'))
          $container.hide()
          $target.parents('p.list-group-item-text').append($container)
          $container.slideDown()
          $target.hide()
          $hide.show()
          Prism.highlightElement($code.get(0))
    else
      $code.slideDown()
      $target.hide()
      $hide.show()
