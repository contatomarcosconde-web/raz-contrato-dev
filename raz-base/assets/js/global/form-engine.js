/**
 * Engine de Formulários (contrato §7-bis).
 * O cliente escreve o <form> completo; aqui injetamos a segurança (honeypot),
 * buscamos um nonce fresco (à prova de cache) e enviamos via REST sem recarregar.
 * Mostra sucesso/erro em .raz-form__msg. Progressive enhancement: requer JS.
 */
( function () {
	'use strict';

	var cfg = window.razForm || {};
	if ( ! cfg.rest ) {
		return;
	}

	var wrappers = Array.prototype.slice.call( document.querySelectorAll( '.raz-form' ) );

	wrappers.forEach( function ( wrap ) {
		var form = wrap.querySelector( 'form' );
		if ( ! form || form.dataset.razReady ) {
			return;
		}
		form.dataset.razReady = '1';

		var id    = wrap.getAttribute( 'data-form-id' );
		var start = Date.now();

		// Honeypot (escondido; só bots preenchem).
		var hp = document.createElement( 'input' );
		hp.type = 'text';
		hp.name = 'raz_hp';
		hp.tabIndex = -1;
		hp.autocomplete = 'off';
		hp.setAttribute( 'aria-hidden', 'true' );
		hp.style.cssText = 'position:absolute!important;left:-9999px!important;top:auto;width:1px;height:1px;opacity:0;pointer-events:none';
		form.appendChild( hp );

		// Container de mensagem (status acessível).
		var msg = wrap.querySelector( '.raz-form__msg' );
		if ( ! msg ) {
			msg = document.createElement( 'div' );
			msg.className = 'raz-form__msg';
			msg.setAttribute( 'role', 'status' );
			msg.setAttribute( 'aria-live', 'polite' );
			form.appendChild( msg );
		}

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			submit( form, id, start, msg );
		} );
	} );

	function setMsg( msg, type, text ) {
		msg.className = 'raz-form__msg is-' + type;
		msg.textContent = text || '';
	}

	function t( key, fallback ) {
		return ( cfg.i18n && cfg.i18n[ key ] ) ? cfg.i18n[ key ] : fallback;
	}

	function submit( form, id, start, msg ) {
		var btn = form.querySelector( '[type="submit"]' );
		if ( btn ) { btn.disabled = true; }
		setMsg( msg, 'loading', t( 'sending', 'Enviando…' ) );

		// 1) nonce fresco (uncached) → 2) envia.
		fetch( cfg.rest + 'nonce', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( d ) {
				var data = new FormData( form );
				data.append( 'raz_form_id', id );
				data.append( 'raz_nonce', d && d.nonce ? d.nonce : '' );
				data.append( 'raz_elapsed', String( Date.now() - start ) );
				data.append( 'raz_origin', window.location.href );
				return fetch( cfg.rest + 'submit', { method: 'POST', body: data, credentials: 'same-origin' } );
			} )
			.then( function ( r ) { return r.json(); } )
			.then( function ( res ) {
				if ( res && res.success ) {
					setMsg( msg, 'success', res.message || t( 'success', 'Enviado!' ) );
					form.reset();
				} else {
					setMsg( msg, 'error', ( res && res.message ) || t( 'error', 'Não foi possível enviar.' ) );
				}
			} )
			.catch( function () {
				setMsg( msg, 'error', t( 'network', 'Erro de conexão. Tente novamente.' ) );
			} )
			.then( function () {
				if ( btn ) { btn.disabled = false; }
			} );
	}
}() );
