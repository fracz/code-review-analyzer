jQuery ($) ->
  $('a#tab-pairs').on 'click', ->
    setTimeout(
      ->
        cy = $('#cy-review-pairs').cytoscape('get')
        cy.resize()
        cy.layout(
          name: 'concentric'
          minNodeSpacing: 60
        )
      1
    )

  $('#cy-review-pairs').cytoscape(
    showOverlay: false
    minZoom: 0.5
    maxZoom: 4
    wheelSensitivity: 0.1
    motionBlur: true
    elements: cy_review_pairs
    style: [
      {
        selector: 'node'
        css:
          'shape': 'roundrectangle'
          'content': 'data(label)'
          'text-valign': 'bottom'
          'font-size': 8
          'background-color': '#ffffff'
          'background-image': 'data(image)'
          'background-fit': 'contain'
          'background-clip': 'node'
          'color': '#333333'
          'border-width': 0
          'width': 25
          'height': 25
      },
      {
        selector: 'edge'
        css:
          'content': 'data(weight)'
          'color': '#3A2831'
          'line-color': '#9F9F9F'
          'font-size': 8
          'target-arrow-color': '#9F9F9F'
          'target-arrow-shape': 'triangle'
          'control-point-step-size': 15
      }
    ]
  )
