jQuery(function ($) {
	var calendarDualBreakpoint = 767;
	var listMode = '';
	var monthMode = '';
	var dualMode =  '';
	var monthDotID = '';

	$(window).on('load', function () {
		$('#main-calendar').prepend($('.path-calendar .lex-region-title'));
		$('main').append($('.path-calendar .lex-region-feedback'));

		if ($(window).width() > calendarDualBreakpoint) {
		dualMode = true;
		modeCheck();
		}else {
		monthMode = true;
		modeCheck();
		}
	});

	$('.list-switch').click(function () {
		listMode = true;
		monthMode = false;
		dualMode = false;

		modeCheck();
	});

	$('.month-switch').click(function () {
		listMode = false;
		monthMode = true;
		dualMode = false;

		modeCheck();
	});

	function modeCheck() {
		if (monthMode === true) {
		$('.month-switch').css({
			'background-color': 'white',
			'box-shadow': 'rgba(0, 0, 0, 0.5) 0px 3px 10px 0px',
			'color': '#004585'
		});
		$('.list-switch').css({
			'background-color': '#EFEFEF',
			'box-shadow': 'unset',
			'color': '#353535'
		});
		$('#sidebar-calendar').css('visibility', 'hidden');
		$('#sidebar-calendar').css('height', '0');

		$('#calendar').css('display', 'block');
		$('.calendar-key').css('display', 'flex');
		$('.calendars-container').height(
			$('.lex-region-title').outerHeight(true) +
			$('.mobile-switch').outerHeight(true) +
			$('#filters').outerHeight(true) +
			$('#calendar-container').outerHeight(true));
		} else if (listMode == true) {
      $('.list-switch').css({
        'background-color': 'white',
        'box-shadow': 'rgba(0, 0, 0, 0.5) 0px 3px 10px 0px',
        'color': '#004585'
      });
      $('.month-switch').css({
        'background-color': '#EFEFEF',
        'box-shadow': 'unset',
        'color': '#353535'
      });
      $('#sidebar-calendar').css({
        'display': 'inline-block',
        'visibility': 'visible',
      });
      $('#calendar').css('display', 'none');
      $('.calendar-key').css('display', 'none');
      $('#sidebar-calendar').height(
        $('.calendars-container').height() - (
          $('.lex-region-title').outerHeight(true) +
          $('.mobile-switch').outerHeight(true) +
          $('#filters').outerHeight(true) +
          $('.lex-region-feedback').outerHeight(true)
          )
      );
		} else if (dualMode == true) {
      $('#calendar').css('display', 'block');
      $('#sidebar-calendar').css('display', 'block');
      $('#sidebar-calendar').css('visibility', 'visible');
      $('.calendar-key').css('display', 'flex');
      $('.calendars-container').height(
        $('.lex-region-title').outerHeight(true) +
        $('#filters').outerHeight(true) +
        $('#calendar-container').outerHeight(true)
      );
      $('#sidebar-calendar').height(
        $('.calendars-container').outerHeight() +
        $('.lex-region-feedback').outerHeight(true)
      );
		}
	}

	$(window).resize(function () {
		if ($(window).width() > calendarDualBreakpoint) {
		if (dualMode == false) {
			dualMode = true;
			monthMode = false;
			listMode = false;
		}
		} else {
		if (dualMode == true) {
			dualMode = false;
			monthMode = true;
			listMode = false;
		}
		}
		modeCheck();
	});

  function scrollToEvent(eventHeading) {
    if (eventHeading) {
      // Scroll the event element to the top of the event list container
      const eventListContainer = document.querySelector('#sidebar-calendar .fc-view-container');
      eventListContainer.scrollTo({
        top: eventHeading.offsetTop,
        behavior: "smooth",
      });
    }
  }

	$(document).on('click', '.month-dot', function () {
		$parentScrollPosition = document.documentElement.scrollTop;
    monthDotID = $(this).attr('id').substr(6);

		if (dualMode == true) {
			$('.list-event-container').each(function () {
        if ($(this).attr('id').substr(5) == monthDotID) {
          var eventHeading = $(this).parent().closest('tr')[0];
          scrollToEvent(eventHeading);
        }
			});
		} else {
			dualMode = false;
			monthMode= false;
			listMode = true;
			$('.list-event-container').each(function () {
        if ($(this).attr('id').substr(5) == monthDotID) {
          var eventHeading = $(this).parent().closest('tr')[0];
          scrollToEvent(eventHeading);
        }
			});
		}

		modeCheck();
		document.documentElement.scrollTop = $parentScrollPosition;
	});

	$(document).on('click', '.main-prev', function () {
		$('.side-prev').trigger('click');
	});
	$(document).on('click', '.main-next', function () {
		$('.side-next').trigger('click');
	});

	if ((window.location.href.indexOf("/events/") >= 0) | window.location.href.indexOf('/meeting-notices/') >= 0) {
		if ($('.lex-breadcrumb-wrapper').find('.usa-unstyled-list').children().length == 3) {
			$('.lex-breadcrumb-wrapper').find('.lex-breadcrumb-item:nth-child(2)').css('display', 'none');
		}

		$('.lex-breadcrumb-wrapper').find('.lex-breadcrumb-item').last().before(
			'<li class="lex-breadcrumb-item"> \
				<span> \
						<a href="/calendar">Calendar</a> \
				</span> \
			</li>'
		);
	}
});
