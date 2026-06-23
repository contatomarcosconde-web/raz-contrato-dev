/**
 * Engine de Popups (contrato §1/§9).
 * Lê os data-* de cada .raz-popup e decide exibir conforme GATILHO e FREQUÊNCIA.
 * A segmentação (onde exibir) e o agendamento já vêm resolvidos do servidor.
 *
 * Gatilhos: load (com atraso) · scroll (X%) · exit (exit-intent).
 * Frequência: every · session · days (N dias) — persistida em storage.
 * Um popup por vez. Acessível: ESC fecha, trava scroll, devolve foco.
 */
( function () {
	'use strict';

	var popups = Array.prototype.slice.call( document.querySelectorAll( '.raz-popup' ) );
	if ( ! popups.length ) {
		return;
	}

	var anyOpen = false;
	var lastFocused = null;

	/* ---------- storage helpers (degrade se indisponível) ---------- */
	function store( type ) {
		try {
			return 'session' === type ? window.sessionStorage : window.localStorage;
		} catch ( e ) {
			return null;
		}
	}
	function keyFor( id ) { return 'raz_popup_seen_' + id; }

	function alreadySeen( el ) {
		var id   = el.getAttribute( 'data-popup-id' );
		var freq = el.getAttribute( 'data-freq' ) || 'session';
		if ( 'every' === freq ) {
			return false;
		}
		if ( 'session' === freq ) {
			var s = store( 'session' );
			return !! ( s && s.getItem( keyFor( id ) ) );
		}
		// days
		var ls = store( 'local' );
		if ( ! ls ) { return false; }
		var ts = parseInt( ls.getItem( keyFor( id ) ), 10 );
		if ( ! ts ) { return false; }
		var days = parseInt( el.getAttribute( 'data-days' ), 10 ) || 7;
		return ( Date.now() - ts ) < ( days * 86400000 );
	}

	function markSeen( el ) {
		var id   = el.getAttribute( 'data-popup-id' );
		var freq = el.getAttribute( 'data-freq' ) || 'session';
		if ( 'session' === freq ) {
			var s = store( 'session' );
			if ( s ) { s.setItem( keyFor( id ), '1' ); }
		} else if ( 'days' === freq ) {
			var ls = store( 'local' );
			if ( ls ) { ls.setItem( keyFor( id ), String( Date.now() ) ); }
		}
	}

	/* ---------- abrir / fechar ---------- */
	function open( el ) {
		anyOpen = true;
		lastFocused = document.activeElement;

		el.hidden = false;
		void el.offsetWidth; // reflow p/ transição
		el.classList.add( 'is-open' );
		document.body.classList.add( 'raz-no-scroll' );
		document.addEventListener( 'keydown', onKeydown );

		var focusTarget = el.querySelector( '.raz-popup__close' ) || el.querySelector( 'a, button, input, textarea' );
		if ( focusTarget ) { focusTarget.focus(); }
	}

	function close( el ) {
		el.classList.remove( 'is-open' );
		document.body.classList.remove( 'raz-no-scroll' );
		document.removeEventListener( 'keydown', onKeydown );
		window.setTimeout( function () {
			if ( ! el.classList.contains( 'is-open' ) ) { el.hidden = true; }
		}, 260 );
		anyOpen = false;
		if ( lastFocused && typeof lastFocused.focus === 'function' ) { lastFocused.focus(); }
	}

	function closeAny() {
		var cur = document.querySelector( '.raz-popup.is-open' );
		if ( cur ) {
			cur.classList.remove( 'is-open' );
			cur.hidden = true;
			document.body.classList.remove( 'raz-no-scroll' );
			document.removeEventListener( 'keydown', onKeydown );
			anyOpen = false;
		}
	}

	function onKeydown( e ) {
		if ( 'Escape' === e.key || 'Esc' === e.key ) {
			var openEl = document.querySelector( '.raz-popup.is-open' );
			if ( openEl ) { close( openEl ); }
		}
	}

	// Auto (gatilho): respeita frequência e "um por vez".
	function tryOpen( el ) {
		if ( anyOpen || alreadySeen( el ) ) { return false; }
		open( el );
		markSeen( el );
		return true;
	}

	// Manual (botão/link/URL): sempre abre, ignora frequência; troca o que estiver aberto.
	function openManual( el ) {
		if ( ! el ) { return; }
		if ( anyOpen ) { closeAny(); }
		open( el );
	}

	function openById( id ) {
		openManual( document.getElementById( 'raz-popup-' + String( id ).replace( /[^0-9]/g, '' ) ) );
	}

	/* ---------- abertura manual: botões/links ---------- */
	document.addEventListener( 'click', function ( e ) {
		var opener = e.target.closest( '[data-raz-popup-open]' );
		if ( opener ) {
			e.preventDefault();
			openById( opener.getAttribute( 'data-raz-popup-open' ) );
		}
	} );

	/* ---------- abertura manual: URL (?raz_popup=ID ou #raz-popup-ID) ---------- */
	( function () {
		var id = null;
		try {
			id = new URLSearchParams( window.location.search ).get( 'raz_popup' );
		} catch ( e ) {}
		if ( ! id && /^#raz-popup-\d+$/.test( window.location.hash ) ) {
			id = window.location.hash.replace( '#raz-popup-', '' );
		}
		if ( id ) {
			openById( id );
		}
	}() );

	/* ---------- ligar gatilhos por popup ---------- */
	popups.forEach( function ( el ) {
		// fechar (botão; e clique no overlay, se houver área)
		el.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( '[data-raz-popup-close]' ) ) { close( el ); }
		} );

		if ( alreadySeen( el ) ) { return; }

		var trigger = el.getAttribute( 'data-trigger' ) || 'load';

		if ( 'load' === trigger ) {
			var delay = ( parseInt( el.getAttribute( 'data-delay' ), 10 ) || 0 ) * 1000;
			window.setTimeout( function () { tryOpen( el ); }, delay );

		} else if ( 'scroll' === trigger ) {
			var pct = parseInt( el.getAttribute( 'data-scroll' ), 10 ) || 50;
			var onScroll = function () {
				var doc = document.documentElement;
				var scrolled = ( doc.scrollTop || document.body.scrollTop );
				var height = ( doc.scrollHeight - doc.clientHeight );
				var current = height > 0 ? ( scrolled / height ) * 100 : 100;
				if ( current >= pct ) {
					window.removeEventListener( 'scroll', onScroll );
					tryOpen( el );
				}
			};
			window.addEventListener( 'scroll', onScroll, { passive: true } );

		} else if ( 'exit' === trigger ) {
			var onExit = function ( e ) {
				if ( e.clientY <= 0 && ! e.relatedTarget ) {
					document.removeEventListener( 'mouseout', onExit );
					tryOpen( el );
				}
			};
			document.addEventListener( 'mouseout', onExit );
		}
	} );
}() );
