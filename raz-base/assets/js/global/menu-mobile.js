/**
 * Drawer mobile acessível (contrato §4/§9).
 * Abre pelo .raz-burger; fecha no overlay, no botão de fechar e com ESC.
 * Gerencia aria-expanded, [hidden], trava de scroll e devolve o foco.
 */
( function () {
	'use strict';

	var burger = document.querySelector( '.raz-burger' );
	var drawer = document.getElementById( 'raz-drawer' );
	if ( ! burger || ! drawer ) {
		return;
	}

	var panel = drawer.querySelector( '.raz-drawer__panel' );
	var lastFocused = null;

	function open() {
		lastFocused = document.activeElement;
		drawer.hidden = false;
		// força reflow para a transição rodar a partir do estado inicial
		void drawer.offsetWidth;
		drawer.classList.add( 'is-open' );
		burger.setAttribute( 'aria-expanded', 'true' );
		document.body.classList.add( 'raz-no-scroll' );
		document.addEventListener( 'keydown', onKeydown );
		var focusable = panel && panel.querySelector( 'a, button' );
		if ( focusable ) {
			focusable.focus();
		}
	}

	function close() {
		drawer.classList.remove( 'is-open' );
		burger.setAttribute( 'aria-expanded', 'false' );
		document.body.classList.remove( 'raz-no-scroll' );
		document.removeEventListener( 'keydown', onKeydown );
		// espera a transição antes de esconder do fluxo/leitor de tela
		window.setTimeout( function () {
			if ( ! drawer.classList.contains( 'is-open' ) ) {
				drawer.hidden = true;
			}
		}, 260 );
		if ( lastFocused && typeof lastFocused.focus === 'function' ) {
			lastFocused.focus();
		}
	}

	function onKeydown( e ) {
		if ( 'Escape' === e.key || 'Esc' === e.key ) {
			close();
		}
	}

	burger.addEventListener( 'click', function () {
		if ( drawer.classList.contains( 'is-open' ) ) {
			close();
		} else {
			open();
		}
	} );

	drawer.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '[data-raz-drawer-close]' ) ) {
			close();
		}
	} );
}() );
