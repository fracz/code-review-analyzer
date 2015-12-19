jQuery ->
  generatedCharts = false
  $('a#tab-statistics').on 'click', ->
    if generatedCharts
      return

    generatedCharts = true
    setTimeout(
      ->
        c3.generate(
          bindto: '#changes-per-review-chart'
          data: changesPerReview
          axis:
            x:
              type: 'category'
          grid:
            y:
              lines: [{value: 0}]
        )
        c3.generate(
          bindto: '#average-comment-length-chart'
          data: averageCommentLength
          axis:
            x:
              type: 'category'
          grid:
            y:
              lines: [{value: 0}]
        )
      1
    )

  c3.generate(
    bindto: '#overall-ranking-chart'
    data: overallRanking
    axis:
      x:
        type: 'category'
    grid:
      y:
        lines: [{value: 0}]
  )
