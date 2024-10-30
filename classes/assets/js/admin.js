(function() {
'use strict'

  function init() {
    if (!window.jQuery) {
      setTimeout(init, 100)
      return;
    }

    const $ = jQuery

    $(function() {
      // Options page
      const $imgQuality = $(`#${CMC.SLUG}-image-quality`)
      const $slider = $('#cmc-images-slider')
      $slider.val($imgQuality.val())

      $imgQuality.on('input', function(e) {
        $slider.val(e.target.value)
      })

      $slider.on('input', function(e) { 
        $imgQuality.val(e.target.value)
      })

      const $enableWebp = $(`#${CMC.ENABLEWEBPID}`)
      $enableWebp[0].disabled = false
      const $form = $(document.forms['options_form'])

      // Enable/disable checkboxes when enable webp support is changed
      $enableWebp.on('change', function(e) {
        const disabled = !e.target.checked
        for (let i = 0; i < $form[0].elements.length; i++ ) {
          if ($form[0].elements[i] == e.target) { continue }
          if ($form[0].elements[i].type == 'checkbox') {
            $form[0].elements[i].disabled = disabled
          }
        }
      })

      // Enable checkboxes on form submit to save values
      $form.on('submit', function(e) {
        $('#submit')[0].disabled = true
        for (let i = 0; i < $form[0].elements.length; i++ ) {
          if ($form[0].elements[i] == e.target) { continue }
          if ($form[0].elements[i].type == 'checkbox') {
            $form[0].elements[i].disabled = false
          }
        }
      })
    })

    // Generation status
    const updateStats = () => {
      $.ajax({
        type: 'post',
        url: ajaxurl,
        data: {
          action: `${CMC.SLUG}-check-status`,
          _ajax_nonce: `${CMC.NONCE}`
        },
        dataType: 'json',
        success: res => {    
          let msg = 'An unknown error occurred.'
          if (res.data.msg) {
            msg = $(`<div>${res.data.msg}</div>`).text()
          }
          if (!res.success) {
            $(`#${CMC.QUALITYTESTID}`).html(`<p align="center">${msg}</p>`)
            $(document.body).trigger('post-load')
            return
          }
          if (!res?.data.last_completed) { 
            $(`#${CMC.QUALITYTESTID}`).html(`<p align="center">${msg}</p>`)
            $(document.body).trigger('post-load')
            return
          } 
          const slug = CMC.SLUG
          $(`#${slug}-num-converted`).html(res.data.converted.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','))
          $(`#${slug}-num-failures`).html(res.data.failures.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','))
          $(`#${slug}-last-completed`).html(res.data.last_completed)
          $(`#${slug}-bytes-saved`).html(res.data.saved.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','))
          const completed = 'Webp conversion has <b>completed.</b>'
          const notCompleted = 'Webp conversion in process.'
          $(`#${slug}-is-completed`).html(res.data.completed ? completed : notCompleted)
          $(document.body).trigger('post-load')
          setTimeout(updateStats, 5000)
        },
        error: e => {
          $(`#${CMC.QUALITYTESTID}`).html('<p align="center">An error occurred attempting to fetch the generation status.</p>')
        }
      })
    }
    updateStats()
  }
  init()
})()
