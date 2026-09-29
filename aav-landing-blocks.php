<?php
/**
 * Plugin Name:       AAV — Landing Blocks
 * Description:        Editable ACF blocks for the AAV premium landing pages. Béatrice edits titles, texts, photos and the form link from the visual editor; the design stays pixel-perfect. Blocks: Alsace, Paris, French Alps, Sustainability, Our Story, The Little Black Book + Landing sur-mesure. Studio de modèles : créer/importer de nouvelles pages depuis l'admin, sans réinstaller l'extension. Works with Secure Custom Fields (SCF) or ACF PRO — both provide the blocks + repeater API.
 * Version:           2.6.1
 * Update URI:        https://github.com/biolay-group/aav-landing-blocks
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Biolay Group
 * License:           GPL-2.0-or-later
 * Text Domain:       aav-lb
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AAV_LB_DIR', plugin_dir_path( __FILE__ ) );

/* ================================================================== *
 * 1. Enregistrement du bloc + admonestation si ACF absent
 * ================================================================== */
add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_register_block_type' ) ) return;

	acf_register_block_type( array(
		'name'            => 'aav-alsace',
		'title'           => __( 'AAV — Landing : Christmas in Alsace', 'aav-lb' ),
		'description'     => __( 'Éditable : textes, photos et lien du formulaire. Structure figée.', 'aav-lb' ),
		'category'        => 'formatting',
		'icon'            => 'palmtree',
		'keywords'        => array( 'aav', 'alsace', 'landing', 'noel' ),
		'mode'            => 'edit',
		'api_version'       => 3,
		'acf_block_version' => 3,
		'render_preview'    => false,
		'supports'        => array( 'align' => false, 'multiple' => false, 'jsx' => true, 'mode' => false ),
		'render_callback' => 'aav_lb_render_alsace',
	) );
} );

add_action( 'admin_notices', function () {
	if ( function_exists( 'acf_register_block_type' ) ) return;
	echo '<div class="notice notice-error"><p><strong>AAV — Landing Blocks :</strong> ce plugin a besoin de <strong>Secure Custom Fields</strong> (ou ACF PRO) pour fonctionner. Vérifie que Secure Custom Fields est bien activé et à jour.</p></div>';
} );

/* ================================================================== *
 * 2. Champs (organisés par onglets ; repeaters pour les sections listées)
 * ================================================================== */
add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	/* petits helpers de construction */
	$T = function ( $key, $label, $default = '', $type = 'text', $instr = '' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 4 ), 'type' => $type, 'default_value' => $default, 'instructions' => $instr );
	};
	$tab = function ( $key, $label ) { return array( 'key' => $key, 'label' => $label, 'type' => 'accordion', 'open' => 0, 'multi_expand' => 1 ); };
	$img = function ( $key, $label, $instr = 'Choisir une image de la médiathèque. Vide = dégradé de secours.' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 4 ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all', 'instructions' => $instr );
	};

	$fields = array();

	/* ---- Hero ---- */
	$fields[] = $tab( 'tab_hero', __( 'Hero', 'aav-lb' ) );
	$fields[] = $img( 'fld_hero_photo', 'Photo du hero' );
	$fields[] = $T( 'fld_hero_kicker', 'Sur-titre', 'Alsace • Christmas Markets' );
	$fields[] = $T( 'fld_hero_title_1', 'Titre — 1re ligne', 'A Winter Written' );
	$fields[] = $T( 'fld_hero_title_2', 'Titre — 2e ligne (en italique)', 'in Lantern Light' );
	$fields[] = $T( 'fld_hero_sub', 'Accroche', 'Private, tailor-made holiday journeys through Strasbourg, Colmar and the storybook villages of the Alsatian Wine Route — where the oldest Christmas market in France still glows exactly as it did in 1570.', 'textarea' );
	$fields[] = $T( 'fld_hero_cta1', 'Bouton principal', 'Plan Your Christmas Escape' );
	$fields[] = $T( 'fld_hero_cta1_url', 'Bouton principal — lien', '#plan', 'text', 'Ancre (#plan) = défile sur la page. Ou URL complète / chemin (/tailor-my-trip/).' );
	$fields[] = $T( 'fld_hero_cta2', 'Bouton secondaire', 'See a Sample Journey' );
	$fields[] = $T( 'fld_hero_cta2_url', 'Bouton secondaire — lien', '#journey', 'text', 'Ancre (#journey) = défile sur la page. Ou URL complète / chemin.' );
	$fields[] = array( 'key' => 'fld_hero_stats', 'label' => 'Chiffres (colonne de droite)', 'name' => 'hero_stats', 'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Ajouter un chiffre', 'instructions' => 'Laisser vide = garder les 3 chiffres par défaut.', 'sub_fields' => array(
		$T( 'fld_stat_v', 'Valeur', '' ), $T( 'fld_stat_l', 'Libellé', '' ),
	) );

	/* ---- Intro ---- */
	$fields[] = $tab( 'tab_intro', __( 'Intro', 'aav-lb' ) );
	$fields[] = $T( 'fld_intro_kicker', 'Sur-titre', 'A Fairytale Winter' );
	$fields[] = $T( 'fld_intro_lead', 'Phrase d’accroche (le mot en italique entre *astérisques*)', 'At Christmas, Alsace stops being a region and becomes *a season one steps into.*', 'textarea' );
	$fields[] = $T( 'fld_intro_body', 'Paragraphe', 'Medieval villages glow with twinkling lights. Historic squares fill with artisan markets that have kept the same rituals for four centuries. The scent of mulled wine, gingerbread and warm spice drifts through cobblestone streets, and behind the timbered façades, cellars are opened for those who know to ask.', 'textarea' );
	$fields[] = $T( 'fld_intro_body2', 'Second paragraphe', 'Our tailor-made journeys are composed entirely around the way you travel — the finest hotels, private guides who unlock the quiet hours, seamless transfers, and the authentic magic of an Alsatian Christmas. Nothing to arrange. Only the pleasure of it.', 'textarea' );

	/* ---- Showcase 1 ---- */
	$fields[] = $tab( 'tab_sc1', __( 'Marchés', 'aav-lb' ) );
	$fields[] = $img( 'fld_sc1_photo', 'Photo' );
	$fields[] = $T( 'fld_sc1_kicker', 'Sur-titre', 'The Markets, Privately' );
	$fields[] = $T( 'fld_sc1_title', 'Titre', 'The Hours' );
	$fields[] = $T( 'fld_sc1_title_em', 'Titre — suite en italique', 'Before the Crowds' );
	$fields[] = $T( 'fld_sc1_p1', 'Paragraphe 1', 'The great markets of Alsace are at their most beautiful in the first hour after the lights come on — and in the last, when the stalls begin to quieten. We arrange privately guided evening walks in precisely those hours, with an expert who knows which craftsman is worth stopping for.', 'textarea' );
	$fields[] = $T( 'fld_sc1_p2', 'Paragraphe 2', 'Afterwards: a table held for you in a historic winstub, far from the boulevard, where the season is celebrated rather than sold.', 'textarea' );

	/* ---- Destinations (mosaïque) ---- */
	$fields[] = $tab( 'tab_dest', __( 'Destinations', 'aav-lb' ) );
	$fields[] = $T( 'fld_dest_kicker', 'Sur-titre', 'Where the Magic Unfolds' );
	$fields[] = $T( 'fld_dest_title', 'Titre', 'Three Jewels of an' );
	$fields[] = $T( 'fld_dest_title_em', 'Titre — suite en italique', 'Alsatian Christmas' );
	$fields[] = $T( 'fld_dest_intro', 'Intro', 'Each has its own character. We weave them into a single, unhurried journey — or build the week around the one that suits you.', 'textarea' );
	$fields[] = array( 'key' => 'fld_dest_items', 'label' => 'Destinations (1re carte = grande)', 'name' => 'dest_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter une destination', 'instructions' => 'Laisser vide = garder les 3 destinations par défaut.', 'sub_fields' => array(
		$img( 'fld_dest_photo', 'Photo' ),
		$T( 'fld_dest_alt', 'Étiquette (petit texte doré)', '' ),
		$T( 'fld_dest_h', 'Titre', '' ),
		$T( 'fld_dest_d', 'Description', '', 'textarea' ),
	) );

	/* ---- Moments (bandes) ---- */
	$fields[] = $tab( 'tab_mom', __( 'Moments', 'aav-lb' ) );
	$fields[] = $T( 'fld_mom_kicker', 'Sur-titre', 'Signature Moments' );
	$fields[] = $T( 'fld_mom_title', 'Titre', 'The Details That' );
	$fields[] = $T( 'fld_mom_title_em', 'Titre — suite en italique', 'Stay With You' );
	$fields[] = array( 'key' => 'fld_mom_items', 'label' => 'Moments', 'name' => 'mom_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un moment', 'instructions' => 'Laisser vide = garder les moments par défaut.', 'sub_fields' => array(
		$T( 'fld_mom_n', 'Numéro (I, II, III…)', '' ),
		$T( 'fld_mom_t', 'Titre', '' ),
		$T( 'fld_mom_d', 'Description', '', 'textarea' ),
	) );

	/* ---- Showcase 2 ---- */
	$fields[] = $tab( 'tab_sc2', __( 'Gastronomie', 'aav-lb' ) );
	$fields[] = $img( 'fld_sc2_photo', 'Photo' );
	$fields[] = $T( 'fld_sc2_kicker', 'Sur-titre', 'La Table' );
	$fields[] = $T( 'fld_sc2_title', 'Titre', 'Where the Season' );
	$fields[] = $T( 'fld_sc2_title_em', 'Titre — suite en italique', 'Is Tasted' );
	$fields[] = $T( 'fld_sc2_p1', 'Paragraphe 1', 'Alsace holds one of the highest concentrations of Michelin stars in France, and in December its kitchens turn to the traditions of the season — game, foie gras, bredele, and wines poured from the slopes just beyond the window.', 'textarea' );
	$fields[] = $T( 'fld_sc2_p2', 'Paragraphe 2', 'We reserve the tables that were full in September, arrange private tastings in cellars that rarely receive visitors, and, when you would rather not move at all, bring a chef to your own table.', 'textarea' );

	/* ---- Itinéraire ---- */
	$fields[] = $tab( 'tab_itin', __( 'Itinéraire', 'aav-lb' ) );
	$fields[] = $T( 'fld_itin_kicker', 'Sur-titre', 'A Glimpse of Your Journey' );
	$fields[] = $T( 'fld_itin_title', 'Titre', 'Five Days in' );
	$fields[] = $T( 'fld_itin_title_em', 'Titre — suite en italique', 'Festive Alsace' );
	$fields[] = array( 'key' => 'fld_itin_days', 'label' => 'Jours', 'name' => 'itin_days', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un jour', 'instructions' => 'Laisser vide = garder l’itinéraire par défaut.', 'sub_fields' => array(
		$T( 'fld_day_n', 'Numéro', '' ),
		$T( 'fld_day_t', 'Titre du jour', '' ),
		$T( 'fld_day_d', 'Description', '', 'textarea' ),
	) );

	/* ---- Séjour ---- */
	$fields[] = $tab( 'tab_stay', __( 'Séjour', 'aav-lb' ) );
	$fields[] = $img( 'fld_stay_photo', 'Photo' );
	$fields[] = $T( 'fld_stay_kicker', 'Sur-titre', 'Where You Will Stay' );
	$fields[] = $T( 'fld_stay_title', 'Titre', 'Houses of Character,' );
	$fields[] = $T( 'fld_stay_title_em', 'Titre — suite en italique', 'Chosen for You' );
	$fields[] = $T( 'fld_stay_intro', 'Intro', 'From grand hotels on the Grande Île to intimate châteaux among the vineyards — we select the property that suits your journey, not the one that suits us.', 'textarea' );
	$fields[] = array( 'key' => 'fld_stay_items', 'label' => 'Prestations incluses', 'name' => 'stay_items', 'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Ajouter une ligne', 'instructions' => 'Laisser vide = garder la liste par défaut.', 'sub_fields' => array(
		$T( 'fld_stay_l', 'Prestation', '' ), $T( 'fld_stay_v', 'Mention (à droite)', '' ),
	) );

	/* ---- Citation ---- */
	$fields[] = $tab( 'tab_quote', __( 'Citation', 'aav-lb' ) );
	$fields[] = $img( 'fld_quote_photo', 'Photo de fond' );
	$fields[] = $T( 'fld_quote_text', 'Citation', 'Every detail was thoughtfully arranged and flawlessly executed. From private château visits to unforgettable dining experiences, we simply enjoyed the journey without ever thinking about logistics.', 'textarea' );
	$fields[] = $T( 'fld_quote_author', 'Auteur', 'Jason M. • A tailor-made AAV journey' );

	/* ---- FAQ ---- */
	$fields[] = $tab( 'tab_faq', __( 'FAQ', 'aav-lb' ) );
	$fields[] = $T( 'fld_faq_kicker', 'Sur-titre', 'Good to Know' );
	$fields[] = $T( 'fld_faq_title', 'Titre', 'Planning Your' );
	$fields[] = $T( 'fld_faq_title_em', 'Titre — suite en italique', 'Alsace Christmas' );
	$fields[] = array( 'key' => 'fld_faq_items', 'label' => 'Questions', 'name' => 'faq_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter une question', 'instructions' => 'Laisser vide = garder la FAQ par défaut. Génère aussi les données structurées (SEO).', 'sub_fields' => array(
		$T( 'fld_faq_q', 'Question', '' ),
		$T( 'fld_faq_a', 'Réponse', '', 'textarea' ),
	) );

	/* ---- Formulaire ---- */
	$fields[] = $tab( 'tab_form', __( 'Formulaire', 'aav-lb' ) );
	$fields[] = $img( 'fld_form_photo', 'Photo de fond' );
	$fields[] = $T( 'fld_form_kicker', 'Sur-titre', 'Begin' );
	$fields[] = $T( 'fld_form_title', 'Titre', 'Plan Your Christmas' );
	$fields[] = $T( 'fld_form_title_em', 'Titre — suite en italique', 'Escape to Alsace' );
	$fields[] = $T( 'fld_form_intro', 'Intro', 'Let us design a personalized holiday journey through the magical Christmas markets of Alsace. Tell us how you like to travel, and one of our specialists will reply within one business day.', 'textarea' );
	$fields[] = $T( 'fld_form_embed', 'Code d’intégration HubSpot (facultatif)', '', 'textarea', 'Colle ici le code d’intégration du formulaire HubSpot. Si vide, un bouton de secours est affiché.' );
	$fields[] = $T( 'fld_form_fb_label', 'Bouton de secours — libellé', 'Start My Christmas Journey' );
	$fields[] = $T( 'fld_form_fb_url', 'Bouton de secours — lien', '/tailor-my-trip/' );

	acf_add_local_field_group( array(
		'key'      => 'group_aav_lb_alsace',
		'title'    => 'AAV — Landing : Christmas in Alsace',
		'fields'   => $fields,
		'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/aav-alsace' ) ) ),
	) );
} );

/* Carte d'apercu sobre affichee dans l'editeur a la place du rendu complet
   (les landings plein ecran destabilisent le canvas ; l'edition se fait via
   le crayon / grand panneau, la previsualisation sur le site). */
function aav_lb_editor_placeholder( $title ) {
	echo '<div style="border:1px dashed #b26b00;border-radius:6px;padding:28px 32px;background:#fffaf3;font-family:-apple-system,sans-serif;">';
	echo '<strong style="font-size:15px;display:block;margin-bottom:6px;">' . esc_html( $title ) . '</strong>';
	echo '<span style="color:#555;font-size:13px;line-height:1.5;display:block;">Aperçu désactivé dans l\'éditeur pour garder l\'admin stable. Éditez les champs via l\'icône crayon (grand panneau) ou la colonne latérale, puis prévisualisez la page sur le site.</span>';
	echo '</div>';
}

/* ================================================================== *
 * 3. Rendu du bloc Alsace
 * ================================================================== */
function aav_lb_render_alsace( $block, $content = '', $is_preview = false ) {

	if ( $is_preview || is_admin() ) {
		aav_lb_editor_placeholder( 'AAV — Landing : Christmas in Alsace' );
		return;
	}

	/* Surcharge HTML du modele : si presente, elle remplace le rendu dynamique */
	if ( aav_lb_maybe_render_override( 'alsace' ) ) return;

	$f  = function ( $name, $default = '' ) { $v = get_field( $name ); return ( $v === '' || $v === null || $v === false ) ? $default : $v; };
	$rows = function ( $name, $fallback ) { $v = get_field( $name ); return ( is_array( $v ) && count( $v ) ) ? $v : $fallback; };

	$img_tag = function ( $id, $alt, $default_url = '' ) {
		$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
		if ( ! $url && $default_url ) { $did = aav_lb_url_to_id( $default_url ); $url = $did ? wp_get_attachment_image_url( $did, 'full' ) : $default_url; }
		if ( ! $url ) return '';
		return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" onerror="this.style.display=\'none\'">';
	};

	/* Photos par défaut (les visuels actuels de la page). La médiathèque prime si un champ image est rempli. */
	$P = 'https://www.aavluxurytravel.com/wp-content/uploads/2026/07/';
	$dp = array(
		'hero'  => $P . 'AdobeStock_277990274-1-scaled.jpeg',
		'sc1'   => $P . 'AdobeStock_1888948884_Editorial_Use_Only-1-scaled.jpeg',
		'dest1' => $P . 'AdobeStock_98809577-1-1-scaled.jpeg',
		'dest2' => $P . 'AdobeStock_285113183-1-scaled.jpeg',
		'dest3' => $P . 'AdobeStock_530105120-2-scaled.jpeg',
		'sc2'   => $P . 'AdobeStock_624497591_Editorial_Use_Only-scaled.jpeg',
		'stay'  => $P . 'AdobeStock_567757701_Editorial_Use_Only-scaled.jpeg',
		'quote' => $P . 'AdobeStock_46822472-scaled.jpeg',
		'form'  => $P . 'AdobeStock_467246685-2-scaled.jpeg',
	);
	$em = function ( $t ) { // *mot* -> <em>mot</em>
		return preg_replace( '/\*(.+?)\*/', '<em>$1</em>', esc_html( $t ) );
	};

	/* --- CSS validée (une seule fois par requête en front) --- */
	static $css_done = false;
	$style = '';
	if ( ! $css_done || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		$css = aav_lb_css( 'alsace' );
		if ( $css ) $style = '<style>' . $css . '</style>';
		$css_done = true;
	}

	/* --- valeurs par défaut des sections répétées --- */
	$def_stats = array(
		array( 'stat_v' => '1570', 'stat_l' => 'The first Christkindelsmärik' ),
		array( 'stat_v' => '170&nbsp;km', 'stat_l' => 'Of Alsatian Wine Route' ),
		array( 'stat_v' => '1:1', 'stat_l' => 'Private guide &amp; concierge' ),
	);
	$def_dest = array(
		array( 'dest_photo' => 0, 'dest_url' => $dp['dest1'], 'dest_alt' => 'Since 1570 • Grande Île', 'dest_h' => 'Strasbourg', 'dest_d' => 'The capital of Christmas. Beneath the soaring Gothic cathedral, the Christkindelsmärik spills across the Grande Île in a glow of lights, evergreen and Alsatian craftsmanship — the oldest market of its kind in France, and still the most magnificent.' ),
		array( 'dest_photo' => 0, 'dest_url' => $dp['dest2'], 'dest_alt' => 'La Petite Venise', 'dest_h' => 'Colmar', 'dest_d' => 'Half-timbered façades, flower-lined canals, and festive reflections shimmering on the water. Intimate where Strasbourg is grand.' ),
		array( 'dest_photo' => 0, 'dest_url' => $dp['dest3'], 'dest_alt' => 'Riquewihr • Eguisheim', 'dest_h' => 'The Wine Route', 'dest_d' => 'Villages crowned among the most beautiful in France, wrapped in holiday décor — with cellars opened privately along the way.' ),
	);
	$def_mom = array(
		array( 'mom_n' => 'I', 'mom_t' => 'A cellar opened after hours', 'mom_d' => 'Private tastings of Alsace’s celebrated Rieslings, Gewürztraminers and Crémants, poured by the winemakers themselves.' ),
		array( 'mom_n' => 'II', 'mom_t' => 'The market before it opens', 'mom_d' => 'Privileged early access with a private guide, while the stallholders are still lighting their lanterns.' ),
		array( 'mom_n' => 'III', 'mom_t' => 'A bredele workshop', 'mom_d' => 'The Christmas biscuits of Alsace, made by hand in a family kitchen — a moment the children will remember for years.' ),
		array( 'mom_n' => 'IV', 'mom_t' => 'The winstub nobody can book', 'mom_d' => 'Historic dining rooms and Michelin-starred tables, reserved months ahead and held for you when they say they are full.' ),
		array( 'mom_n' => 'V', 'mom_t' => 'A château in the vineyards', 'mom_d' => 'Nights in refined properties full of character and warmth, with a fire lit and the vines under snow beyond the window.' ),
	);
	$def_days = array(
		array( 'day_n' => '1', 'day_t' => 'Arrival in Strasbourg', 'day_d' => 'A private transfer to your hotel on the Grande Île, then an evening walk through the illuminated Christkindelsmärik with your guide, and a first dinner in a historic winstub.' ),
		array( 'day_n' => '2', 'day_t' => 'Strasbourg, Unhurried', 'day_d' => 'A privileged morning at the cathedral before the doors fill, artisan discoveries across the old town, and an afternoon left entirely to you.' ),
		array( 'day_n' => '3', 'day_t' => 'The Canals of Colmar', 'day_d' => 'South to Colmar and La Petite Venise, with private artisan encounters and a festive lunch, before the lights come on across the water.' ),
		array( 'day_n' => '4', 'day_t' => 'The Wine Route', 'day_d' => 'Riquewihr, Eguisheim and Kaysersberg by private car, with a cellar opened for you and a tasting among the vines under snow.' ),
		array( 'day_n' => '5', 'day_t' => 'Farewell to Alsace', 'day_d' => 'A last morning among the stalls, then a seamless transfer onward — or an extension to Paris, the French Alps, or the houses of Champagne.' ),
	);
	$def_stay = array(
		array( 'stay_l' => 'Luxury hotels &amp; boutique properties', 'stay_v' => 'Hand-selected' ),
		array( 'stay_l' => 'Private transfers throughout', 'stay_v' => 'Included' ),
		array( 'stay_l' => 'Expert local guides', 'stay_v' => 'Included' ),
		array( 'stay_l' => 'Private tastings &amp; gourmet experiences', 'stay_v' => 'By design' ),
		array( 'stay_l' => 'Dedicated AAV concierge', 'stay_v' => 'Throughout your stay' ),
	);
	$def_faq = array(
		array( 'faq_q' => 'When do the Christmas markets in Alsace take place?', 'faq_a' => 'The Alsatian Christmas markets generally run from the last week of November through the end of December. Strasbourg’s Christkindelsmärik, first held in 1570, is the oldest of its kind in France. We tailor each journey to the finest dates — and to the quietest hours of the day.' ),
		array( 'faq_q' => 'Which Alsatian towns have the most beautiful markets?', 'faq_a' => 'Strasbourg and Colmar are the grand highlights, while the Wine Route villages — Riquewihr, Eguisheim and Kaysersberg — offer a more intimate, storybook charm.' ),
		array( 'faq_q' => 'Is an Alsace Christmas journey suitable for families?', 'faq_a' => 'Beautifully so. We design private journeys for every generation, with experiences and pacing matched to your family — from bredele biscuit workshops to horse-drawn moments through the vineyards.' ),
		array( 'faq_q' => 'How far in advance should we book?', 'faq_a' => 'This is a peak season with limited luxury availability. The finest hotels, private guides and starred tables are secured several months ahead.' ),
		array( 'faq_q' => 'Can we combine Alsace with other destinations?', 'faq_a' => 'Yes. Alsace pairs naturally with Christmas in Paris, the houses of Champagne, the Black Forest, or a staffed chalet in the French Alps — all arranged as a single, seamless journey.' ),
	);

	$stats = $rows( 'hero_stats', $def_stats );
	$dest  = $rows( 'dest_items', $def_dest );
	$mom   = $rows( 'mom_items', $def_mom );
	$days  = $rows( 'itin_days', $def_days );
	$stay  = $rows( 'stay_items', $def_stay );
	$faq   = $rows( 'faq_items', $def_faq );

	$phcls = array( 'als-ph', 'als-ph--warm', 'als-ph--night' ); // rotation d'ambiance mosaïque

	ob_start();
	echo $style;
	?>
	<div class="als-root">

	<!-- HERO -->
	<section class="als-hero als-full">
		<div class="als-ph als-ph--night als-cover"><?php echo $img_tag( $f( 'hero_photo' ), 'Christmas market in Alsace at dusk', $dp['hero'] ); ?></div>
		<div class="als-hero__veil"></div><div class="als-hero__veil2"></div>
		<div class="als-hero__inner"><div class="als-hero__grid">
			<div>
				<span class="als-kicker"><?php echo esc_html( $f( 'hero_kicker' ) ); ?></span>
				<h1 class="als-hero__title"><?php echo esc_html( $f( 'hero_title_1' ) ); ?><br><em><?php echo esc_html( $f( 'hero_title_2' ) ); ?></em></h1>
				<p class="als-hero__sub"><?php echo esc_html( $f( 'hero_sub' ) ); ?></p>
				<div class="als-hero__cta">
					<a href="<?php echo aav_lb_cta_url( $f( 'hero_cta1_url' ), '#plan' ); ?>" class="als-btn als-btn--champ"><?php echo esc_html( $f( 'hero_cta1' ) ); ?></a>
					<a href="<?php echo aav_lb_cta_url( $f( 'hero_cta2_url' ), '#journey' ); ?>" class="als-btn als-btn--wire"><?php echo esc_html( $f( 'hero_cta2' ) ); ?></a>
				</div>
			</div>
			<div class="als-stats">
				<?php foreach ( $stats as $s ) : ?>
					<div class="als-stat"><strong><?php echo wp_kses_post( $s['stat_v'] ); ?></strong><span><?php echo wp_kses_post( $s['stat_l'] ); ?></span></div>
				<?php endforeach; ?>
			</div>
		</div></div>
	</section>

	<!-- INTRO -->
	<section class="als-manifesto"><div class="als-wrap"><div class="als-manifesto__grid">
		<div class="als-numeral">01</div>
		<div>
			<span class="als-kicker als-kicker--wine"><?php echo esc_html( $f( 'intro_kicker' ) ); ?></span>
			<p class="als-lead"><?php echo $em( $f( 'intro_lead' ) ); ?></p>
			<p class="als-body" style="margin-bottom:16px;"><?php echo esc_html( $f( 'intro_body' ) ); ?></p>
			<p class="als-body"><?php echo esc_html( $f( 'intro_body2' ) ); ?></p>
		</div>
	</div></div></section>

	<!-- SHOWCASE 1 -->
	<section class="als-showcase als-full">
		<div class="als-showcase__media"><div class="als-ph als-ph--warm als-cover"><?php echo $img_tag( $f( 'sc1_photo' ), 'Lantern-lit market at night', $dp['sc1'] ); ?></div></div>
		<div class="als-showcase__body"><div>
			<span class="als-kicker"><?php echo esc_html( $f( 'sc1_kicker' ) ); ?></span>
			<h2><?php echo esc_html( $f( 'sc1_title' ) ); ?> <em><?php echo esc_html( $f( 'sc1_title_em' ) ); ?></em></h2>
			<p><?php echo esc_html( $f( 'sc1_p1' ) ); ?></p>
			<p><?php echo esc_html( $f( 'sc1_p2' ) ); ?></p>
		</div></div>
	</section>

	<!-- DESTINATIONS -->
	<section class="als-places"><div class="als-wrap">
		<div class="als-head">
			<span class="als-kicker als-kicker--wine"><?php echo esc_html( $f( 'dest_kicker' ) ); ?></span>
			<h2 class="als-h2"><?php echo esc_html( $f( 'dest_title' ) ); ?> <em><?php echo esc_html( $f( 'dest_title_em' ) ); ?></em></h2>
			<p class="als-body" style="margin-top:20px;"><?php echo esc_html( $f( 'dest_intro' ) ); ?></p>
		</div>
		<div class="als-grid">
			<?php foreach ( array_values( $dest ) as $i => $d ) :
				$cls = $i === 0 ? 'als-ph' : $phcls[ $i % 3 ];
				$tall = $i === 0 ? ' als-p--tall' : ''; ?>
				<div class="als-p<?php echo $tall; ?>">
					<div class="<?php echo esc_attr( $cls ); ?> als-cover"><?php echo $img_tag( $d['dest_photo'], $d['dest_h'], isset( $d['dest_url'] ) ? $d['dest_url'] : '' ); ?></div>
					<div class="als-p__veil"></div>
					<div class="als-p__c">
						<span class="als-p__alt"><?php echo wp_kses_post( $d['dest_alt'] ); ?></span>
						<h3><?php echo esc_html( $d['dest_h'] ); ?></h3>
						<p><?php echo esc_html( $d['dest_d'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- MOMENTS -->
	<section class="als-moments"><div class="als-wrap">
		<div class="als-head--center" style="max-width:660px;">
			<span class="als-kicker"><?php echo esc_html( $f( 'mom_kicker' ) ); ?></span>
			<h2 class="als-h2 als-h2--light"><?php echo esc_html( $f( 'mom_title' ) ); ?> <em><?php echo esc_html( $f( 'mom_title_em' ) ); ?></em></h2>
		</div>
		<div>
			<?php foreach ( $mom as $m ) : ?>
				<div class="als-strip"><div class="als-strip__n"><?php echo esc_html( $m['mom_n'] ); ?></div><div class="als-strip__t"><?php echo esc_html( $m['mom_t'] ); ?></div><div class="als-strip__d"><?php echo esc_html( $m['mom_d'] ); ?></div></div>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- SHOWCASE 2 -->
	<section class="als-showcase als-showcase--flip als-full">
		<div class="als-showcase__media"><div class="als-ph als-ph--warm als-cover"><?php echo $img_tag( $f( 'sc2_photo' ), 'Festive Alsatian table', $dp['sc2'] ); ?></div></div>
		<div class="als-showcase__body"><div>
			<span class="als-kicker"><?php echo esc_html( $f( 'sc2_kicker' ) ); ?></span>
			<h2><?php echo esc_html( $f( 'sc2_title' ) ); ?> <em><?php echo esc_html( $f( 'sc2_title_em' ) ); ?></em></h2>
			<p><?php echo esc_html( $f( 'sc2_p1' ) ); ?></p>
			<p><?php echo esc_html( $f( 'sc2_p2' ) ); ?></p>
		</div></div>
	</section>

	<!-- ITINÉRAIRE -->
	<section id="journey" class="als-itin"><div class="als-wrap">
		<div class="als-head--center" style="max-width:640px;">
			<span class="als-kicker als-kicker--wine"><?php echo esc_html( $f( 'itin_kicker' ) ); ?></span>
			<h2 class="als-h2"><?php echo esc_html( $f( 'itin_title' ) ); ?> <em><?php echo esc_html( $f( 'itin_title_em' ) ); ?></em></h2>
			<hr class="als-rule" />
		</div>
		<div class="als-days">
			<?php foreach ( $days as $d ) : ?>
				<div class="als-day"><div class="als-day__n"><?php echo esc_html( $d['day_n'] ); ?><span>Day</span></div><div class="als-day__c"><h3><?php echo esc_html( $d['day_t'] ); ?></h3><p><?php echo esc_html( $d['day_d'] ); ?></p></div></div>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- SÉJOUR -->
	<section class="als-stay"><div class="als-wrap" style="padding:0;"><div class="als-stay__grid">
		<div class="als-stay__media"><div class="als-ph als-ph--night als-cover"><?php echo $img_tag( $f( 'stay_photo' ), 'Refined Alsatian hotel in winter', $dp['stay'] ); ?></div></div>
		<div class="als-stay__body">
			<span class="als-kicker als-kicker--wine"><?php echo esc_html( $f( 'stay_kicker' ) ); ?></span>
			<h2 class="als-h2" style="font-size:clamp(26px,3.4vw,38px);"><?php echo esc_html( $f( 'stay_title' ) ); ?> <em><?php echo esc_html( $f( 'stay_title_em' ) ); ?></em></h2>
			<p class="als-body" style="margin-top:18px;"><?php echo esc_html( $f( 'stay_intro' ) ); ?></p>
			<ul class="als-list">
				<?php foreach ( $stay as $it ) : ?>
					<li><b><?php echo wp_kses_post( $it['stay_l'] ); ?></b><span><?php echo esc_html( $it['stay_v'] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div></div></section>

	<!-- CITATION -->
	<section class="als-quote als-full">
		<div class="als-ph als-ph--warm als-cover"><?php echo $img_tag( $f( 'quote_photo' ), 'Alsatian village under snow', $dp['quote'] ); ?></div>
		<div class="als-quote__veil"></div>
		<div class="als-quote__c">
			<p class="als-quote__t">&ldquo;<?php echo esc_html( $f( 'quote_text' ) ); ?>&rdquo;</p>
			<div class="als-quote__a"><?php echo esc_html( $f( 'quote_author' ) ); ?></div>
		</div>
	</section>

	<!-- FAQ -->
	<section class="als-faq-sec"><div class="als-wrap">
		<div class="als-head--center" style="max-width:620px;">
			<span class="als-kicker als-kicker--wine"><?php echo esc_html( $f( 'faq_kicker' ) ); ?></span>
			<h2 class="als-h2"><?php echo esc_html( $f( 'faq_title' ) ); ?> <em><?php echo esc_html( $f( 'faq_title_em' ) ); ?></em></h2>
		</div>
		<div class="als-faq">
			<?php foreach ( array_values( $faq ) as $i => $qa ) : ?>
				<details<?php echo $i === 0 ? ' open' : ''; ?>><summary><?php echo esc_html( $qa['faq_q'] ); ?></summary><p><?php echo esc_html( $qa['faq_a'] ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- FORM -->
	<section id="plan" class="als-plan als-full">
		<div class="als-ph als-ph--night als-cover"><?php echo $img_tag( $f( 'form_photo' ), 'Alsatian Christmas market at night', $dp['form'] ); ?></div>
		<div class="als-plan__veil"></div>
		<div class="als-plan__inner">
			<div class="als-plan__l">
				<span class="als-kicker"><?php echo esc_html( $f( 'form_kicker' ) ); ?></span>
				<h2><?php echo esc_html( $f( 'form_title' ) ); ?> <em><?php echo esc_html( $f( 'form_title_em' ) ); ?></em></h2>
				<p><?php echo esc_html( $f( 'form_intro' ) ); ?></p>
			</div>
			<div class="als-form">
				<?php $embed = $f( 'form_embed' );
				if ( trim( $embed ) !== '' ) {
					echo $embed; // code d'intégration HubSpot fourni par l'admin
				} else { ?>
					<div style="text-align:center;">
						<p style="font-size:16px;color:#8A8078;margin:0 0 24px;line-height:1.85;">Share a few details and we will be in touch within 24 hours.</p>
						<a href="<?php echo esc_url( $f( 'form_fb_url', '/tailor-my-trip/' ) ); ?>" class="als-btn als-btn--wine"><?php echo esc_html( $f( 'form_fb_label' ) ); ?></a>
					</div>
				<?php } ?>
			</div>
		</div>
	</section>

	</div>
	<?php
	/* Données structurées FAQ (SEO) */
	$mainentity = array();
	foreach ( $faq as $qa ) {
		$mainentity[] = array(
			'@type' => 'Question', 'name' => wp_strip_all_tags( $qa['faq_q'] ),
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $qa['faq_a'] ) ),
		);
	}
	$schema = array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $mainentity );
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';

	echo ob_get_clean();
}

/* ================================================================== *
 * BLOC 2 — Christmas in Paris
 * ================================================================== */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) return;
	acf_register_block_type( array(
		'name'            => 'aav-paris',
		'title'           => __( 'AAV — Landing : Christmas in Paris', 'aav-lb' ),
		'description'     => __( 'Éditable : textes, photos et lien du formulaire. Structure figée.', 'aav-lb' ),
		'category'        => 'formatting',
		'icon'            => 'palmtree',
		'keywords'        => array( 'aav', 'paris', 'landing', 'noel' ),
		'mode'            => 'edit',
		'api_version'       => 3,
		'acf_block_version' => 3,
		'render_preview'    => false,
		'supports'        => array( 'align' => false, 'multiple' => false, 'jsx' => true, 'mode' => false ),
		'render_callback' => 'aav_lb_render_paris',
	) );
} );

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$T = function ( $key, $label, $default = '', $type = 'text', $instr = '' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => $type, 'default_value' => $default, 'instructions' => $instr );
	};
	$tab = function ( $key, $label ) { return array( 'key' => $key, 'label' => $label, 'type' => 'accordion', 'open' => 0, 'multi_expand' => 1 ); };
	$img = function ( $key, $label ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all', 'instructions' => 'Vide = la photo actuelle de la page reste affichée.' );
	};

	$f = array();

	/* Hero */
	$f[] = $tab( 'ptab_hero', 'Hero' );
	$f[] = $img( 'pfld_hero_photo', 'Photo du hero' );
	$f[] = $T( 'pfld_hero_kicker', 'Sur-titre', 'Paris • The Festive Season' );
	$f[] = $T( 'pfld_hero_title', 'Titre', 'Christmas in Paris' );
	$f[] = $T( 'pfld_hero_title_em', 'Titre — 2e ligne (italique)', 'An Art de Vivre' );
	$f[] = $T( 'pfld_hero_sub', 'Accroche', 'Private, tailor-made journeys through a city dressed for winter — illuminated avenues, couture windows, palace suites, and tables that few will ever be seated at.', 'textarea' );
	$f[] = $T( 'pfld_hero_cta', 'Bouton', 'Compose Your Parisian Christmas' );
	$f[] = $T( 'pfld_hero_cta_url', 'Bouton — lien', '#plan', 'text', 'Ancre (#plan) = défile sur la page. Ou URL complète / chemin.' );

	/* Ouverture */
	$f[] = $tab( 'ptab_open', 'Ouverture' );
	$f[] = $T( 'pfld_open_kicker', 'Sur-titre', 'The City, Transformed' );
	$f[] = $T( 'pfld_open_lead', 'Accroche', 'There is a moment, sometime in late November, when Paris quietly changes register. The great avenues take on their lights, the windows of the grands magasins become theatre, and the city’s finest addresses begin to smell of chestnut, cinnamon, and old wood. It is the same Paris — only more so.', 'textarea' );
	$f[] = $T( 'pfld_open_body', 'Paragraphe', 'We compose each festive journey with the same care a couturier gives a fitting: the suite with the view that matters, the private view of the Louvre before the doors open, the table at the restaurant that has been full since September. Nothing rushed, nothing generic — a Christmas shaped entirely around you.', 'textarea' );

	/* Triptyque */
	$f[] = $tab( 'ptab_tri', 'Triptyque' );
	$f[] = array( 'key' => 'pfld_tri_items', 'label' => 'Volets (3)', 'name' => 'tri_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un volet', 'instructions' => 'Vide = garder les 3 volets par défaut.', 'sub_fields' => array(
		$img( 'pfld_tri_photo', 'Photo' ),
		$T( 'pfld_tri_num', 'Numéro', '' ),
		$T( 'pfld_tri_title', 'Titre', '' ),
		$T( 'pfld_tri_desc', 'Description', '', 'textarea' ),
	) );

	/* Bandeau */
	$f[] = $tab( 'ptab_band', 'Bandeau' );
	$f[] = $img( 'pfld_band_photo', 'Photo de fond' );
	$f[] = $T( 'pfld_band_text', 'Citation', '“Paris in December is not a destination. It is a season one is invited into.”', 'textarea' );

	/* Split 1 */
	$f[] = $tab( 'ptab_sc1', 'Gastronomie' );
	$f[] = $img( 'pfld_sc1_photo', 'Photo' );
	$f[] = $T( 'pfld_sc1_kicker', 'Sur-titre', 'La Table' );
	$f[] = $T( 'pfld_sc1_title', 'Titre', 'The Tables of' );
	$f[] = $T( 'pfld_sc1_title_em', 'Titre — italique', 'December' );
	$f[] = $T( 'pfld_sc1_p1', 'Paragraphe 1', 'Paris holds more Michelin stars than any city in France, and in December its kitchens are at their most inspired — truffle, game, oysters from Brittany, bûches signed by the great pâtissiers.', 'textarea' );
	$f[] = $T( 'pfld_sc1_p2', 'Paragraphe 2', 'We reserve the rooms that were full months ago, arrange private tastings and chef’s tables, and — when you would rather stay in — bring a chef to your suite for a réveillon that belongs to you alone.', 'textarea' );

	/* Split 2 */
	$f[] = $tab( 'ptab_sc2', 'Accès privilégié' );
	$f[] = $img( 'pfld_sc2_photo', 'Photo' );
	$f[] = $T( 'pfld_sc2_kicker', 'Sur-titre', 'Privileged Access' );
	$f[] = $T( 'pfld_sc2_title', 'Titre', 'Paris,' );
	$f[] = $T( 'pfld_sc2_title_em', 'Titre — italique', 'Before the Doors Open' );
	$f[] = $T( 'pfld_sc2_p1', 'Paragraphe 1', 'A private morning in the Louvre before the public arrives. A box at the Opéra Garnier. An atelier visit with a maître d’art who rarely receives visitors.', 'textarea' );
	$f[] = $T( 'pfld_sc2_p2', 'Paragraphe 2', 'Our relationships in Paris are decades old — and they are what turn a beautiful holiday into one that could not have been arranged any other way.', 'textarea' );

	/* Séjour */
	$f[] = $tab( 'ptab_sej', 'Séjour' );
	$f[] = $T( 'pfld_sej_kicker', 'Sur-titre', 'A Glimpse of Your Stay' );
	$f[] = $T( 'pfld_sej_title', 'Titre', 'Five Days,' );
	$f[] = $T( 'pfld_sej_title_em', 'Titre — italique', 'Composed' );
	$f[] = array( 'key' => 'pfld_idx_items', 'label' => 'Jours', 'name' => 'idx_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un jour', 'instructions' => 'Vide = garder l’itinéraire par défaut.', 'sub_fields' => array(
		$T( 'pfld_idx_num', 'Numéro (i, ii…)', '' ),
		$T( 'pfld_idx_title', 'Titre', '' ),
		$T( 'pfld_idx_desc', 'Description', '', 'textarea' ),
	) );

	/* Adresses */
	$f[] = $tab( 'ptab_addr', 'Adresses' );
	$f[] = $T( 'pfld_addr_kicker', 'Sur-titre', 'Where You Will Stay' );
	$f[] = $T( 'pfld_addr_title', 'Titre', 'The Great Houses of' );
	$f[] = $T( 'pfld_addr_title_em', 'Titre — italique', 'Paris' );
	$f[] = $T( 'pfld_addr_intro', 'Intro', 'We work with every palace and grande maison in the city — and we choose the one that suits you, not the one that suits us.', 'textarea' );
	$f[] = array( 'key' => 'pfld_addr_items', 'label' => 'Cartes (2)', 'name' => 'addr_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter une carte', 'instructions' => 'Vide = garder les 2 cartes par défaut.', 'sub_fields' => array(
		$img( 'pfld_addr_photo', 'Photo' ),
		$T( 'pfld_addr_num', 'Étiquette (ex. Right Bank)', '' ),
		$T( 'pfld_addr_h', 'Titre', '' ),
		$T( 'pfld_addr_d', 'Description', '', 'textarea' ),
	) );

	/* Témoignage */
	$f[] = $tab( 'ptab_quote', 'Témoignage' );
	$f[] = $T( 'pfld_quote_text', 'Citation', 'From the first conversation to our return flight, every detail was handled beautifully. The experience felt effortless, personal, and completely stress-free.', 'textarea' );
	$f[] = $T( 'pfld_quote_author', 'Auteur', 'Jennifer W. • Paris • Brittany • London' );

	/* FAQ */
	$f[] = $tab( 'ptab_faq', 'FAQ' );
	$f[] = $T( 'pfld_faq_kicker', 'Sur-titre', 'Good to Know' );
	$f[] = $T( 'pfld_faq_title', 'Titre', 'Planning Your' );
	$f[] = $T( 'pfld_faq_title_em', 'Titre — italique', 'Parisian Christmas' );
	$f[] = array( 'key' => 'pfld_faq_items', 'label' => 'Questions', 'name' => 'faq_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter une question', 'instructions' => 'Vide = garder la FAQ par défaut. Génère aussi les données structurées (SEO).', 'sub_fields' => array(
		$T( 'pfld_faq_q', 'Question', '' ),
		$T( 'pfld_faq_a', 'Réponse', '', 'textarea' ),
	) );

	/* Formulaire */
	$f[] = $tab( 'ptab_form', 'Formulaire' );
	$f[] = $img( 'pfld_form_photo', 'Photo de fond' );
	$f[] = $T( 'pfld_form_kicker', 'Sur-titre', 'Begin' );
	$f[] = $T( 'pfld_form_title', 'Titre', 'Compose Your' );
	$f[] = $T( 'pfld_form_title_em', 'Titre — italique', 'Parisian Christmas' );
	$f[] = $T( 'pfld_form_intro', 'Intro', 'Tell us how you would like to spend the season, and one of our Paris specialists will design it entirely around you.', 'textarea' );
	$f[] = $T( 'pfld_form_embed', 'Code d’intégration HubSpot (facultatif)', '', 'textarea', 'Si vide, un bouton de secours est affiché.' );
	$f[] = $T( 'pfld_form_fb_label', 'Bouton de secours — libellé', 'Start My Parisian Christmas' );
	$f[] = $T( 'pfld_form_fb_url', 'Bouton de secours — lien', '/tailor-my-trip/' );

	acf_add_local_field_group( array(
		'key'      => 'group_aav_lb_paris',
		'title'    => 'AAV — Landing : Christmas in Paris',
		'fields'   => $f,
		'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/aav-paris' ) ) ),
	) );
} );

function aav_lb_render_paris( $block, $content = '', $is_preview = false ) {

	if ( $is_preview || is_admin() ) {
		aav_lb_editor_placeholder( 'AAV — Landing : Christmas in Paris' );
		return;
	}

	/* Surcharge HTML du modele : si presente, elle remplace le rendu dynamique */
	if ( aav_lb_maybe_render_override( 'paris' ) ) return;

	$f   = function ( $n, $d = '' ) { $v = get_field( $n ); return ( $v === '' || $v === null || $v === false ) ? $d : $v; };
	$rows = function ( $n, $fb ) { $v = get_field( $n ); return ( is_array( $v ) && count( $v ) ) ? $v : $fb; };
	$img_tag = function ( $id, $alt, $default_url = '' ) {
		$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
		if ( ! $url && $default_url ) { $did = aav_lb_url_to_id( $default_url ); $url = $did ? wp_get_attachment_image_url( $did, 'full' ) : $default_url; }
		if ( ! $url ) return '';
		return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" onerror="this.style.display=\'none\'">';
	};

	$P = 'https://www.aavluxurytravel.com/wp-content/uploads/2026/07/';
	$dp = array(
		'hero'  => $P . 'AdobeStock_239094391-scaled.jpeg',
		'tri1'  => $P . 'AdobeStock_333559108_Editorial_Use_Only-2-scaled.jpeg',
		'tri2'  => $P . 'AdobeStock_370595074-scaled.jpeg',
		'tri3'  => $P . 'AdobeStock_1752282447_Editorial_Use_Only-scaled.jpeg',
		'band'  => $P . 'AdobeStock_239350641-scaled.jpeg',
		'sc1'   => $P . 'AdobeStock_205955056-scaled.jpeg',
		'sc2'   => $P . 'AdobeStock_726495710_Editorial_Use_Only-1-scaled.jpeg',
		'addr1' => $P . 'AdobeStock_427411890_Editorial_Use_Only-scaled.jpeg',
		'addr2' => $P . 'AdobeStock_362459026_Editorial_Use_Only-2-scaled.jpeg',
	);

	$def_tri = array(
		array( 'tri_photo' => 0, 'u' => $dp['tri1'], 'ph' => '', 'tri_num' => 'I', 'tri_title' => 'The Couture Windows', 'tri_desc' => 'The animated vitrines of the grands magasins — a Parisian institution since 1909 — visited privately, before the boulevard fills.' ),
		array( 'tri_photo' => 0, 'u' => $dp['tri2'], 'ph' => 'prs-ph--soft', 'tri_num' => 'II', 'tri_title' => 'Avenues of Light', 'tri_desc' => 'From the Champs-Élysées to the quiet, lantern-lit streets of the Marais, the city rendered in gold.' ),
		array( 'tri_photo' => 0, 'u' => $dp['tri3'], 'ph' => 'prs-ph--night', 'tri_num' => 'III', 'tri_title' => 'A Palace to Come Home To', 'tri_desc' => 'The great houses of Paris at their most festive — a fire, a tree, and a suite chosen for the view it gives you.' ),
	);
	$def_idx = array(
		array( 'idx_num' => 'i', 'idx_title' => 'Arrival & the Lights', 'idx_desc' => 'Private transfer to your palace suite, then an evening drive along the illuminated avenues with your guide, ending at a table on the Left Bank.' ),
		array( 'idx_num' => 'ii', 'idx_title' => 'The Louvre, Privately', 'idx_desc' => 'A morning in the museum before the doors open, followed by lunch in a historic dining room and an afternoon at leisure.' ),
		array( 'idx_num' => 'iii', 'idx_title' => 'Couture & the Grands Magasins', 'idx_desc' => 'A private shopping experience with a personal styliste, the festive windows, and tea at a Parisian institution.' ),
		array( 'idx_num' => 'iv', 'idx_title' => 'Versailles in Winter', 'idx_desc' => 'The château and its gardens under a low December sun, without the crowds — a private guide, a chauffeur, and time to linger.' ),
		array( 'idx_num' => 'v', 'idx_title' => 'Réveillon', 'idx_desc' => 'A festive dinner composed for you — at one of the city’s great tables, or in the privacy of your own suite.' ),
	);
	$def_addr = array(
		array( 'addr_photo' => 0, 'u' => $dp['addr1'], 'ph' => 'prs-ph--night', 'addr_num' => 'Right Bank', 'addr_h' => 'Grandeur & Couture', 'addr_d' => 'The legendary addresses of the 1st and 8th — steps from the Place Vendôme, the Faubourg Saint-Honoré, and the finest windows in the city.' ),
		array( 'addr_photo' => 0, 'u' => $dp['addr2'], 'ph' => 'prs-ph--soft', 'addr_num' => 'Left Bank', 'addr_h' => 'Intimacy & Character', 'addr_d' => 'Saint-Germain and the 7th — discreet houses with fires lit in the salon, for those who prefer Paris whispered rather than announced.' ),
	);
	$def_faq = array(
		array( 'faq_q' => 'When is the best time to visit Paris at Christmas?', 'faq_a' => 'The festive season in Paris begins in late November, when the avenue lights and the animated windows of the grands magasins are unveiled, and continues through early January. Early December is the most serene; the week of Christmas and New Year the most atmospheric — and the most sought after.' ),
		array( 'faq_q' => 'What makes a luxury Christmas in Paris different?', 'faq_a' => 'Access. Private, after-hours visits to museums, tables reserved months in advance, a personal styliste for the couture houses, and a palace suite chosen for its view. We remove every queue, every wait, and every arrangement from your holiday.' ),
		array( 'faq_q' => 'Is Paris at Christmas suitable for families?', 'faq_a' => 'Beautifully so. We design private journeys for all generations — the festive windows and carousels for the youngest, ateliers and pâtisserie workshops for the curious, and a pace that suits everyone travelling together.' ),
		array( 'faq_q' => 'Can we combine Paris with other destinations?', 'faq_a' => 'Yes. Paris pairs naturally with the Christmas markets of Alsace, the châteaux of the Loire, the French Alps, or London — all seamlessly arranged by AAV, with private transfers and first-class rail throughout.' ),
		array( 'faq_q' => 'How far in advance should we book?', 'faq_a' => 'The finest suites and the most celebrated tables for December are secured six to twelve months ahead. The earlier we begin, the more the season is yours to shape.' ),
	);

	$tri  = $rows( 'tri_items', $def_tri );
	$idx  = $rows( 'idx_items', $def_idx );
	$addr = $rows( 'addr_items', $def_addr );
	$faq  = $rows( 'faq_items', $def_faq );

	$css = aav_lb_css( 'paris' );
	ob_start();
	if ( $css ) echo '<style>' . $css . '</style>';
	?>
	<div class="prs-root">

	<!-- HERO -->
	<section class="prs-hero prs-full">
		<div class="prs-ph prs-ph--night prs-cover"><?php echo $img_tag( $f( 'hero_photo' ), 'Paris illuminated for Christmas', $dp['hero'] ); ?></div>
		<div class="prs-hero__veil"></div><div class="prs-hero__frame"></div>
		<div class="prs-hero__inner">
			<span class="prs-eyebrow prs-hero__eyebrow"><?php echo esc_html( $f( 'hero_kicker' ) ); ?></span>
			<h1 class="prs-hero__title"><?php echo esc_html( $f( 'hero_title' ) ); ?><em><?php echo esc_html( $f( 'hero_title_em' ) ); ?></em></h1>
			<p class="prs-hero__sub"><?php echo esc_html( $f( 'hero_sub' ) ); ?></p>
			<a href="<?php echo aav_lb_cta_url( $f( 'hero_cta_url' ), '#plan' ); ?>" class="prs-btn prs-btn--light"><?php echo esc_html( $f( 'hero_cta' ) ); ?></a>
		</div>
	</section>

	<!-- OUVERTURE -->
	<section class="prs-open"><div class="prs-narrow">
		<span class="prs-eyebrow" style="text-align:center;"><?php echo esc_html( $f( 'open_kicker' ) ); ?></span>
		<p class="prs-open__lead"><?php echo esc_html( $f( 'open_lead' ) ); ?></p>
		<p class="prs-body"><?php echo esc_html( $f( 'open_body' ) ); ?></p>
		<div class="prs-hair" style="margin-top:44px;"></div>
	</div></section>

	<!-- TRIPTYQUE -->
	<section style="padding:0 0 120px;"><div class="prs-wrap"><div class="prs-tri">
		<?php foreach ( $tri as $t ) : $ph = isset( $t['ph'] ) ? $t['ph'] : ''; $u = isset( $t['u'] ) ? $t['u'] : ''; ?>
			<div class="prs-tri__i">
				<div class="prs-ph <?php echo esc_attr( $ph ); ?>"><?php echo $img_tag( $t['tri_photo'], $t['tri_title'], $u ); ?></div>
				<div class="prs-tri__c">
					<span class="prs-num"><?php echo esc_html( $t['tri_num'] ); ?></span>
					<h3><?php echo esc_html( $t['tri_title'] ); ?></h3>
					<p><?php echo esc_html( $t['tri_desc'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div></div></section>

	<!-- BANDEAU -->
	<section class="prs-band prs-full">
		<div class="prs-ph prs-ph--soft prs-cover"><?php echo $img_tag( $f( 'band_photo' ), 'Paris under snow', $dp['band'] ); ?></div>
		<div class="prs-band__veil"></div>
		<div class="prs-band__c"><p class="prs-band__t"><?php echo esc_html( $f( 'band_text' ) ); ?></p></div>
	</section>

	<!-- SPLIT 1 -->
	<section class="prs-split prs-full">
		<div class="prs-split__media"><div class="prs-ph prs-ph--night prs-cover"><?php echo $img_tag( $f( 'sc1_photo' ), 'A starred Parisian table', $dp['sc1'] ); ?></div></div>
		<div class="prs-split__body">
			<span class="prs-eyebrow"><?php echo esc_html( $f( 'sc1_kicker' ) ); ?></span>
			<h2><?php echo esc_html( $f( 'sc1_title' ) ); ?> <em><?php echo esc_html( $f( 'sc1_title_em' ) ); ?></em></h2>
			<p class="prs-body" style="margin-bottom:18px;"><?php echo esc_html( $f( 'sc1_p1' ) ); ?></p>
			<p class="prs-body"><?php echo esc_html( $f( 'sc1_p2' ) ); ?></p>
		</div>
	</section>

	<!-- SPLIT 2 -->
	<section class="prs-split prs-split--flip prs-full">
		<div class="prs-split__media"><div class="prs-ph prs-cover"><?php echo $img_tag( $f( 'sc2_photo' ), 'A private visit before opening', $dp['sc2'] ); ?></div></div>
		<div class="prs-split__body">
			<span class="prs-eyebrow"><?php echo esc_html( $f( 'sc2_kicker' ) ); ?></span>
			<h2><?php echo esc_html( $f( 'sc2_title' ) ); ?> <em><?php echo esc_html( $f( 'sc2_title_em' ) ); ?></em></h2>
			<p class="prs-body" style="margin-bottom:18px;"><?php echo esc_html( $f( 'sc2_p1' ) ); ?></p>
			<p class="prs-body"><?php echo esc_html( $f( 'sc2_p2' ) ); ?></p>
		</div>
	</section>

	<!-- SÉJOUR -->
	<section class="prs-sejour"><div class="prs-wrap">
		<div style="text-align:center;max-width:640px;margin:0 auto 66px;">
			<span class="prs-eyebrow"><?php echo esc_html( $f( 'sej_kicker' ) ); ?></span>
			<h2 class="prs-h2"><?php echo esc_html( $f( 'sej_title' ) ); ?> <em><?php echo esc_html( $f( 'sej_title_em' ) ); ?></em></h2>
			<hr class="prs-rule" />
		</div>
		<div class="prs-index">
			<?php foreach ( $idx as $d ) : ?>
				<div class="prs-index__row"><div class="prs-index__n"><?php echo esc_html( $d['idx_num'] ); ?></div><div class="prs-index__t"><?php echo esc_html( $d['idx_title'] ); ?></div><div class="prs-index__d"><?php echo esc_html( $d['idx_desc'] ); ?></div></div>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- ADRESSES -->
	<section style="padding:120px 0;"><div class="prs-wrap">
		<div style="text-align:center;max-width:640px;margin:0 auto 60px;">
			<span class="prs-eyebrow"><?php echo esc_html( $f( 'addr_kicker' ) ); ?></span>
			<h2 class="prs-h2"><?php echo esc_html( $f( 'addr_title' ) ); ?> <em><?php echo esc_html( $f( 'addr_title_em' ) ); ?></em></h2>
			<p class="prs-body" style="margin-top:20px;"><?php echo esc_html( $f( 'addr_intro' ) ); ?></p>
		</div>
		<div class="prs-addr">
			<?php foreach ( $addr as $a ) : $ph = isset( $a['ph'] ) ? $a['ph'] : ''; $u = isset( $a['u'] ) ? $a['u'] : ''; ?>
				<div class="prs-card">
					<div class="prs-ph <?php echo esc_attr( $ph ); ?> prs-cover"><?php echo $img_tag( $a['addr_photo'], $a['addr_h'], $u ); ?></div>
					<div class="prs-card__veil"></div>
					<div class="prs-card__c">
						<span class="prs-num"><?php echo esc_html( $a['addr_num'] ); ?></span>
						<h3><?php echo esc_html( $a['addr_h'] ); ?></h3>
						<p><?php echo esc_html( $a['addr_d'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- TÉMOIGNAGE -->
	<section class="prs-quote prs-full"><div class="prs-wrap">
		<div class="prs-hair" style="background:var(--gold-l);margin-bottom:10px;"></div>
		<p class="prs-quote__t"><?php echo esc_html( $f( 'quote_text' ) ); ?></p>
		<div class="prs-quote__a"><?php echo esc_html( $f( 'quote_author' ) ); ?></div>
	</div></section>

	<!-- FAQ -->
	<section class="prs-faq-sec"><div class="prs-wrap">
		<div style="text-align:center;max-width:600px;margin:0 auto 60px;">
			<span class="prs-eyebrow"><?php echo esc_html( $f( 'faq_kicker' ) ); ?></span>
			<h2 class="prs-h2"><?php echo esc_html( $f( 'faq_title' ) ); ?> <em><?php echo esc_html( $f( 'faq_title_em' ) ); ?></em></h2>
		</div>
		<div class="prs-faq">
			<?php foreach ( array_values( $faq ) as $i => $qa ) : ?>
				<details<?php echo $i === 0 ? ' open' : ''; ?>><summary><?php echo esc_html( $qa['faq_q'] ); ?></summary><p><?php echo esc_html( $qa['faq_a'] ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- FORM -->
	<section id="plan" class="prs-plan prs-full">
		<div class="prs-ph prs-ph--night prs-cover"><?php echo $img_tag( $f( 'form_photo' ), 'Paris at night', '' ); ?></div>
		<div class="prs-plan__veil"></div>
		<div class="prs-plan__inner">
			<span class="prs-eyebrow" style="color:var(--gold-l);"><?php echo esc_html( $f( 'form_kicker' ) ); ?></span>
			<h2><?php echo esc_html( $f( 'form_title' ) ); ?> <em><?php echo esc_html( $f( 'form_title_em' ) ); ?></em></h2>
			<p class="prs-plan__sub"><?php echo esc_html( $f( 'form_intro' ) ); ?></p>
			<div class="prs-form">
				<?php $embed = $f( 'form_embed' );
				if ( trim( $embed ) !== '' ) { echo $embed; } else { ?>
					<p style="font-size:15.5px;color:#8A8378;margin:0 0 26px;line-height:1.9;">Share a few details and we will be in touch within 24 hours.</p>
					<a href="<?php echo esc_url( $f( 'form_fb_url', '/tailor-my-trip/' ) ); ?>" class="prs-btn prs-btn--ink"><?php echo esc_html( $f( 'form_fb_label' ) ); ?></a>
				<?php } ?>
			</div>
		</div>
	</section>

	</div>
	<?php
	$me = array();
	foreach ( $faq as $qa ) $me[] = array( '@type' => 'Question', 'name' => wp_strip_all_tags( $qa['faq_q'] ), 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $qa['faq_a'] ) ) );
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $me ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
	echo ob_get_clean();
}

/* ================================================================== *
 * BLOC 3 — Winter in the French Alps
 * ================================================================== */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) return;
	acf_register_block_type( array(
		'name'            => 'aav-alps',
		'title'           => __( 'AAV — Landing : Winter in the French Alps', 'aav-lb' ),
		'description'     => __( 'Éditable : textes, photos et lien du formulaire. Structure figée.', 'aav-lb' ),
		'category'        => 'formatting',
		'icon'            => 'palmtree',
		'keywords'        => array( 'aav', 'alps', 'alpes', 'ski', 'landing' ),
		'mode'            => 'edit',
		'api_version'       => 3,
		'acf_block_version' => 3,
		'render_preview'    => false,
		'supports'        => array( 'align' => false, 'multiple' => false, 'jsx' => true, 'mode' => false ),
		'render_callback' => 'aav_lb_render_alps',
	) );
} );

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$T = function ( $key, $label, $default = '', $type = 'text', $instr = '' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => $type, 'default_value' => $default, 'instructions' => $instr );
	};
	$tab = function ( $key, $label ) { return array( 'key' => $key, 'label' => $label, 'type' => 'accordion', 'open' => 0, 'multi_expand' => 1 ); };
	$img = function ( $key, $label ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all', 'instructions' => 'Vide = la photo actuelle de la page reste affichée.' );
	};

	$f = array();

	/* Hero */
	$f[] = $tab( 'atab_hero', 'Hero' );
	$f[] = $img( 'afld_hero_photo', 'Photo du hero' );
	$f[] = $T( 'afld_hero_kicker', 'Sur-titre', 'French Alps • Winter Season' );
	$f[] = $T( 'afld_hero_title_1', 'Titre — 1re ligne', 'Above the Clouds,' );
	$f[] = $T( 'afld_hero_title_2', 'Titre — 2e ligne (italique)', 'Beyond the Ordinary' );
	$f[] = $T( 'afld_hero_sub', 'Accroche', 'Private, tailor-made winter journeys through Courchevel, Val d’Isère, Méribel and Chamonix — where first tracks at dawn, Michelin-starred tables, and a chalet of your own become a single, effortless whole.', 'textarea' );
	$f[] = $T( 'afld_hero_cta1', 'Bouton principal', 'Design My Alpine Winter' );
	$f[] = $T( 'afld_hero_cta1_url', 'Bouton principal — lien', '#plan', 'text', 'Ancre (#plan) = défile sur la page. Ou URL complète / chemin.' );
	$f[] = $T( 'afld_hero_cta2', 'Bouton secondaire', 'Explore the Resorts' );
	$f[] = $T( 'afld_hero_cta2_url', 'Bouton secondaire — lien', '#resorts', 'text', 'Ancre (#resorts) = défile sur la page. Ou URL complète / chemin.' );
	$f[] = array( 'key' => 'afld_hero_stats', 'label' => 'Chiffres (colonne de droite)', 'name' => 'hero_stats', 'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Ajouter un chiffre', 'instructions' => 'Vide = garder les 3 chiffres par défaut.', 'sub_fields' => array(
		$T( 'afld_stat_v', 'Valeur', '' ), $T( 'afld_stat_l', 'Libellé', '' ),
	) );

	/* Manifesto */
	$f[] = $tab( 'atab_man', 'Manifesto' );
	$f[] = $T( 'afld_man_kicker', 'Sur-titre', 'The Winter We Design' );
	$f[] = $T( 'afld_man_lead', 'Accroche', 'Snow is only the beginning. The French Alps, at their finest, are a study in contrast — the silence of a dawn descent, the warmth of a fire-lit chalet, the precision of a three-star kitchen at 2,000 metres.', 'textarea' );
	$f[] = $T( 'afld_man_body', 'Paragraphe', 'We compose each winter journey around the way you actually travel: the rhythm of your days, the level of your skiing, the tables you would love to be seated at, the moments your family will still talk about years from now. Private guides who know which slope will be empty at eleven. A chalet team who learn how you take your coffee. Transfers that simply appear. Nothing to arrange, nothing to chase — only the mountain, and the pleasure of it.', 'textarea' );

	/* Showcase 1 */
	$f[] = $tab( 'atab_sc1', 'Première trace' );
	$f[] = $img( 'afld_sc1_photo', 'Photo' );
	$f[] = $T( 'afld_sc1_kicker', 'Sur-titre', 'The Mountain, Privately' );
	$f[] = $T( 'afld_sc1_title', 'Titre', 'First Tracks Before the World Wakes' );
	$f[] = $T( 'afld_sc1_p1', 'Paragraphe 1', 'Lifts opened early, a private ski instructor at your side, and an entire face of untouched powder in front of you. We arrange privileged early access and off-piste guiding with mountain professionals who read the snow like a language.', 'textarea' );
	$f[] = $T( 'afld_sc1_p2', 'Paragraphe 2', 'By mid-morning, when the slopes begin to fill, you are already somewhere else — a quiet mountain restaurant, a helicopter transfer, a hidden valley reachable only with a guide.', 'textarea' );

	/* Resorts */
	$f[] = $tab( 'atab_res', 'Stations' );
	$f[] = $T( 'afld_res_kicker', 'Sur-titre', 'Where You Will Stay' );
	$f[] = $T( 'afld_res_title', 'Titre', 'Four Peaks,' );
	$f[] = $T( 'afld_res_title_em', 'Titre — italique', 'Four Characters' );
	$f[] = $T( 'afld_res_sub', 'Intro', 'Each resort in the French Alps has its own temperament. We match you to the one that fits — or weave several into a single seamless winter.', 'textarea' );
	$f[] = array( 'key' => 'afld_res_items', 'label' => 'Stations (1re carte = grande)', 'name' => 'res_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter une station', 'instructions' => 'Vide = garder les 4 stations par défaut.', 'sub_fields' => array(
		$img( 'afld_res_photo', 'Photo' ),
		$T( 'afld_res_alt', 'Étiquette (altitude / domaine)', '' ),
		$T( 'afld_res_h', 'Nom', '' ),
		$T( 'afld_res_d', 'Description', '', 'textarea' ),
	) );

	/* Moments */
	$f[] = $tab( 'atab_mom', 'Moments' );
	$f[] = $T( 'afld_mom_kicker', 'Sur-titre', 'Signature Moments' );
	$f[] = $T( 'afld_mom_title', 'Titre', 'The Details That' );
	$f[] = $T( 'afld_mom_title_em', 'Titre — italique', 'Stay With You' );
	$f[] = array( 'key' => 'afld_mom_items', 'label' => 'Moments', 'name' => 'mom_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un moment', 'instructions' => 'Vide = garder les moments par défaut.', 'sub_fields' => array(
		$T( 'afld_mom_n', 'Numéro (I, II…)', '' ),
		$T( 'afld_mom_t', 'Titre', '' ),
		$T( 'afld_mom_d', 'Description', '', 'textarea' ),
	) );

	/* Chalet */
	$f[] = $tab( 'atab_cha', 'Chalet' );
	$f[] = $img( 'afld_cha_photo', 'Photo' );
	$f[] = $T( 'afld_cha_kicker', 'Sur-titre', 'Your Chalet' );
	$f[] = $T( 'afld_cha_title', 'Titre', 'A Home in the Mountains,' );
	$f[] = $T( 'afld_cha_title_em', 'Titre — italique', 'Staffed for You' );
	$f[] = $T( 'afld_cha_sub', 'Intro', 'We select from the finest private chalets and palace suites in the Alps — then staff them so completely that the week runs itself.', 'textarea' );
	$f[] = array( 'key' => 'afld_cha_items', 'label' => 'Prestations incluses', 'name' => 'cha_items', 'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Ajouter une ligne', 'instructions' => 'Vide = garder la liste par défaut.', 'sub_fields' => array(
		$T( 'afld_cha_l', 'Prestation', '' ), $T( 'afld_cha_v', 'Mention (à droite)', '' ),
	) );

	/* Showcase 2 */
	$f[] = $tab( 'atab_sc2', 'Après / Table' );
	$f[] = $img( 'afld_sc2_photo', 'Photo' );
	$f[] = $T( 'afld_sc2_kicker', 'Sur-titre', 'Après, Elevated' );
	$f[] = $T( 'afld_sc2_title', 'Titre', 'Where the Day Softens' );
	$f[] = $T( 'afld_sc2_p1', 'Paragraphe 1', 'The Alps hold more Michelin stars per square kilometre than almost anywhere in France. We reserve the rooms worth crossing a valley for — and, when you would rather not move at all, we bring the kitchen to your chalet.', 'textarea' );
	$f[] = $T( 'afld_sc2_p2', 'Paragraphe 2', 'Afterwards: a spa ritual as the light fades, an open fire, and the particular quiet of a mountain evening with nothing left to organise.', 'textarea' );

	/* Quote */
	$f[] = $tab( 'atab_quote', 'Citation' );
	$f[] = $img( 'afld_quote_photo', 'Photo de fond' );
	$f[] = $T( 'afld_quote_text', 'Citation', 'One of our best trips ever. Beautiful hotels, exceptional service, and a private driver who made every day feel smooth and relaxed.', 'textarea' );
	$f[] = $T( 'afld_quote_author', 'Auteur', 'Charlene P. • A tailor-made AAV journey' );

	/* FAQ */
	$f[] = $tab( 'atab_faq', 'FAQ' );
	$f[] = $T( 'afld_faq_kicker', 'Sur-titre', 'Good to Know' );
	$f[] = $T( 'afld_faq_title', 'Titre', 'Planning Your' );
	$f[] = $T( 'afld_faq_title_em', 'Titre — italique', 'Alpine Winter' );
	$f[] = array( 'key' => 'afld_faq_items', 'label' => 'Questions', 'name' => 'faq_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter une question', 'instructions' => 'Vide = garder la FAQ par défaut. Génère aussi les données structurées (SEO).', 'sub_fields' => array(
		$T( 'afld_faq_q', 'Question', '' ),
		$T( 'afld_faq_a', 'Réponse', '', 'textarea' ),
	) );

	/* Formulaire */
	$f[] = $tab( 'atab_form', 'Formulaire' );
	$f[] = $img( 'afld_form_photo', 'Photo de fond' );
	$f[] = $T( 'afld_form_kicker', 'Sur-titre', 'Begin' );
	$f[] = $T( 'afld_form_title', 'Titre', 'Design Your Alpine Winter' );
	$f[] = $T( 'afld_form_intro', 'Intro', 'Tell us how you like to travel — the resort, the rhythm, the people beside you. We will compose a winter that belongs entirely to you.', 'textarea' );
	$f[] = $T( 'afld_form_embed', 'Code d’intégration HubSpot (facultatif)', '', 'textarea', 'Si vide, un bouton de secours est affiché.' );
	$f[] = $T( 'afld_form_fb_label', 'Bouton de secours — libellé', 'Start My Alpine Journey' );
	$f[] = $T( 'afld_form_fb_url', 'Bouton de secours — lien', '/tailor-my-trip/' );

	acf_add_local_field_group( array(
		'key'      => 'group_aav_lb_alps',
		'title'    => 'AAV — Landing : Winter in the French Alps',
		'fields'   => $f,
		'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/aav-alps' ) ) ),
	) );
} );

function aav_lb_render_alps( $block, $content = '', $is_preview = false ) {

	if ( $is_preview || is_admin() ) {
		aav_lb_editor_placeholder( 'AAV — Landing : Winter in the French Alps' );
		return;
	}

	/* Surcharge HTML du modele : si presente, elle remplace le rendu dynamique */
	if ( aav_lb_maybe_render_override( 'alps' ) ) return;

	$f   = function ( $n, $d = '' ) { $v = get_field( $n ); return ( $v === '' || $v === null || $v === false ) ? $d : $v; };
	$rows = function ( $n, $fb ) { $v = get_field( $n ); return ( is_array( $v ) && count( $v ) ) ? $v : $fb; };
	$img_tag = function ( $id, $alt, $default_url = '' ) {
		$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
		if ( ! $url && $default_url ) { $did = aav_lb_url_to_id( $default_url ); $url = $did ? wp_get_attachment_image_url( $did, 'full' ) : $default_url; }
		if ( ! $url ) return '';
		return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" onerror="this.style.display=\'none\'">';
	};

	$P = 'https://www.aavluxurytravel.com/wp-content/uploads/2026/07/';
	$dp = array(
		'hero'  => $P . 'AdobeStock_326486950-scaled.jpeg',
		'sc1'   => $P . 'AdobeStock_1878173023-1.jpeg',
		'res1'  => $P . 'AdobeStock_243683803-1-scaled.jpeg',
		'res2'  => $P . 'valdisere.jpg',
		'res3'  => $P . 'AdobeStock_255781024-1-scaled.jpeg',
		'res4'  => $P . 'AdobeStock_179872175-1-scaled.jpeg',
		'cha'   => $P . 'AdobeStock_1041650015-1-scaled.jpeg',
		'sc2'   => $P . 'AdobeStock_298961285-scaled.jpeg',
		'quote' => $P . 'AdobeStock_498019979-1-scaled.jpeg',
		'form'  => $P . 'AdobeStock_407482210-1-scaled.jpeg',
	);

	$def_stats = array(
		array( 'stat_v' => '3,230&nbsp;m', 'stat_l' => 'Highest lifted peak' ),
		array( 'stat_v' => '600&nbsp;km', 'stat_l' => 'Linked pistes, Three Valleys' ),
		array( 'stat_v' => '1:1', 'stat_l' => 'Private guide &amp; concierge' ),
	);
	$def_res = array(
		array( 'res_photo' => 0, 'u' => $dp['res1'], 'ph' => '', 'tall' => true, 'res_alt' => '1,850 m • Three Valleys', 'res_h' => 'Courchevel', 'res_d' => 'The most refined address in the Alps — palace hotels, more Michelin stars than any resort on earth, and impeccably groomed slopes that lead you home to the door of your chalet.' ),
		array( 'res_photo' => 0, 'u' => $dp['res2'], 'ph' => 'alp-ph--dusk', 'tall' => false, 'res_alt' => '1,850 m • Espace Killy', 'res_h' => 'Val d’Isère', 'res_d' => 'For those who came to ski. Legendary terrain, snow-sure to the last week of the season, and an après that never feels forced.' ),
		array( 'res_photo' => 0, 'u' => $dp['res3'], 'ph' => 'alp-ph--snow', 'tall' => false, 'res_alt' => '1,450 m • Three Valleys', 'res_h' => 'Méribel', 'res_d' => 'Wood, warmth, and the heart of the world’s largest ski area — the most graceful choice for families travelling together.' ),
		array( 'res_photo' => 0, 'u' => $dp['res4'], 'ph' => '', 'tall' => false, 'res_alt' => '1,035 m • Mont-Blanc', 'res_h' => 'Chamonix', 'res_d' => 'Raw alpine majesty beneath the roof of Europe — for travellers who want the mountain to feel immense.' ),
	);
	$def_mom = array(
		array( 'mom_n' => 'I', 'mom_t' => 'Heli-transfer to a glacier lunch', 'mom_d' => 'Lift off from the valley and land where there is nothing but snow, silence, and a table set for you alone.' ),
		array( 'mom_n' => 'II', 'mom_t' => 'A private ski instructor, all week', 'mom_d' => 'Whether you are carving your first turns or chasing couloirs, one professional who adapts to you — not a timetable.' ),
		array( 'mom_n' => 'III', 'mom_t' => 'A chef in your chalet', 'mom_d' => 'Savoyard classics or haute cuisine, prepared in your own kitchen and served precisely when you come off the mountain.' ),
		array( 'mom_n' => 'IV', 'mom_t' => 'The table nobody can book', 'mom_d' => 'Michelin-starred rooms at altitude, reserved months ahead — and held for you when they say they are full.' ),
		array( 'mom_n' => 'V', 'mom_t' => 'Dog sled through the pines at dusk', 'mom_d' => 'For the children, for the grandparents, for anyone who has ever wanted a winter that feels like a story.' ),
	);
	$def_cha = array(
		array( 'cha_l' => 'Private chef &amp; daily housekeeping', 'cha_v' => 'Included' ),
		array( 'cha_l' => 'Chauffeured transfers &amp; in-resort driver', 'cha_v' => 'Included' ),
		array( 'cha_l' => 'Ski-in / ski-out or door-to-slope service', 'cha_v' => 'By design' ),
		array( 'cha_l' => 'Spa, hammam, indoor pool', 'cha_v' => 'Selected properties' ),
		array( 'cha_l' => 'Dedicated AAV concierge', 'cha_v' => 'Throughout your stay' ),
	);
	$def_faq = array(
		array( 'faq_q' => 'When is the best time to ski in the French Alps?', 'faq_a' => 'The season generally runs from December to mid-April. Christmas and February half-term bring the most festive atmosphere, while January and late March offer quieter slopes and exceptional value. High-altitude resorts such as Val d’Isère and Courchevel remain snow-sure to the end of the season.' ),
		array( 'faq_q' => 'Which French ski resort is best for luxury travellers?', 'faq_a' => 'Courchevel 1850 is the most refined address in the Alps, with palace hotels and an extraordinary concentration of Michelin-starred restaurants. Val d’Isère suits committed skiers, Méribel families, and Chamonix those drawn to raw alpine grandeur.' ),
		array( 'faq_q' => 'Do we need to be experienced skiers?', 'faq_a' => 'Not at all. Private instructors are matched to your level, from first turns to off-piste couloirs — and many of our travellers spend as much time in the spa, at the table, and on a dog sled as they do on the slopes.' ),
		array( 'faq_q' => 'Can you arrange a private chalet with staff?', 'faq_a' => 'Yes. We select from the finest private chalets in the Alps and staff them fully — private chef, housekeeping, chauffeur, and a dedicated AAV concierge throughout your stay.' ),
		array( 'faq_q' => 'How far in advance should we book?', 'faq_a' => 'The finest chalets and palace suites for Christmas, New Year, and February are secured six to twelve months ahead. The earlier we begin, the more freedom you have.' ),
	);

	$stats = $rows( 'hero_stats', $def_stats );
	$res   = $rows( 'res_items', $def_res );
	$mom   = $rows( 'mom_items', $def_mom );
	$cha   = $rows( 'cha_items', $def_cha );
	$faq   = $rows( 'faq_items', $def_faq );

	$css = aav_lb_css( 'alps' );
	ob_start();
	if ( $css ) echo '<style>' . $css . '</style>';
	?>
	<div class="alp-root">

	<!-- HERO -->
	<section class="alp-hero alp-full">
		<div class="alp-ph alp-cover"><?php echo $img_tag( $f( 'hero_photo' ), 'French Alps at dawn', $dp['hero'] ); ?></div>
		<div class="alp-hero__veil"></div><div class="alp-hero__veil2"></div>
		<div class="alp-hero__inner"><div class="alp-hero__grid">
			<div>
				<span class="alp-kicker"><?php echo esc_html( $f( 'hero_kicker' ) ); ?></span>
				<h1 class="alp-hero__title"><?php echo esc_html( $f( 'hero_title_1' ) ); ?><br><em><?php echo esc_html( $f( 'hero_title_2' ) ); ?></em></h1>
				<p class="alp-hero__sub"><?php echo esc_html( $f( 'hero_sub' ) ); ?></p>
				<div class="alp-hero__cta">
					<a href="<?php echo aav_lb_cta_url( $f( 'hero_cta1_url' ), '#plan' ); ?>" class="alp-btn alp-btn--champ"><?php echo esc_html( $f( 'hero_cta1' ) ); ?></a>
					<a href="<?php echo aav_lb_cta_url( $f( 'hero_cta2_url' ), '#resorts' ); ?>" class="alp-btn alp-btn--wire"><?php echo esc_html( $f( 'hero_cta2' ) ); ?></a>
				</div>
			</div>
			<div class="alp-stats">
				<?php foreach ( $stats as $s ) : ?>
					<div class="alp-stat"><strong><?php echo wp_kses_post( $s['stat_v'] ); ?></strong><span><?php echo wp_kses_post( $s['stat_l'] ); ?></span></div>
				<?php endforeach; ?>
			</div>
		</div></div>
	</section>

	<!-- MANIFESTO -->
	<section class="alp-manifesto"><div class="alp-wrap"><div class="alp-manifesto__grid">
		<div class="alp-numeral">01</div>
		<div>
			<span class="alp-kicker"><?php echo esc_html( $f( 'man_kicker' ) ); ?></span>
			<p class="alp-lead"><?php echo esc_html( $f( 'man_lead' ) ); ?></p>
			<p class="alp-body"><?php echo esc_html( $f( 'man_body' ) ); ?></p>
		</div>
	</div></div></section>

	<!-- SHOWCASE 1 -->
	<section class="alp-showcase alp-full">
		<div class="alp-showcase__media"><div class="alp-ph alp-ph--snow alp-cover"><?php echo $img_tag( $f( 'sc1_photo' ), 'First tracks at dawn', $dp['sc1'] ); ?></div></div>
		<div class="alp-showcase__body"><div>
			<span class="alp-kicker"><?php echo esc_html( $f( 'sc1_kicker' ) ); ?></span>
			<h2><?php echo esc_html( $f( 'sc1_title' ) ); ?></h2>
			<p><?php echo esc_html( $f( 'sc1_p1' ) ); ?></p>
			<p><?php echo esc_html( $f( 'sc1_p2' ) ); ?></p>
		</div></div>
	</section>

	<!-- RESORTS -->
	<section id="resorts" class="alp-resorts"><div class="alp-wrap">
		<div class="alp-head">
			<span class="alp-kicker"><?php echo esc_html( $f( 'res_kicker' ) ); ?></span>
			<h2 class="alp-h2"><?php echo esc_html( $f( 'res_title' ) ); ?> <em><?php echo esc_html( $f( 'res_title_em' ) ); ?></em></h2>
			<p class="alp-sub"><?php echo esc_html( $f( 'res_sub' ) ); ?></p>
		</div>
		<div class="alp-rgrid">
			<?php foreach ( $res as $r ) :
				$ph = isset( $r['ph'] ) ? $r['ph'] : '';
				$u  = isset( $r['u'] ) ? $r['u'] : '';
				$tall = ! empty( $r['tall'] ) ? ' alp-r--tall' : ''; ?>
				<div class="alp-r<?php echo $tall; ?>">
					<div class="alp-ph <?php echo esc_attr( $ph ); ?> alp-cover"><?php echo $img_tag( $r['res_photo'], $r['res_h'], $u ); ?></div>
					<div class="alp-r__veil"></div>
					<div class="alp-r__c">
						<span class="alp-r__alt"><?php echo wp_kses_post( $r['res_alt'] ); ?></span>
						<h3><?php echo esc_html( $r['res_h'] ); ?></h3>
						<p><?php echo esc_html( $r['res_d'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- SIGNATURE MOMENTS -->
	<section class="alp-moments"><div class="alp-wrap">
		<div class="alp-head--center">
			<span class="alp-kicker"><?php echo esc_html( $f( 'mom_kicker' ) ); ?></span>
			<h2 class="alp-h2"><?php echo esc_html( $f( 'mom_title' ) ); ?> <em><?php echo esc_html( $f( 'mom_title_em' ) ); ?></em></h2>
		</div>
		<div>
			<?php foreach ( $mom as $m ) : ?>
				<div class="alp-strip"><div class="alp-strip__n"><?php echo esc_html( $m['mom_n'] ); ?></div><div class="alp-strip__t"><?php echo esc_html( $m['mom_t'] ); ?></div><div class="alp-strip__d"><?php echo esc_html( $m['mom_d'] ); ?></div></div>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- CHALET -->
	<section class="alp-chalet"><div class="alp-wrap"><div class="alp-chalet__grid">
		<div class="alp-chalet__media"><div class="alp-ph alp-ph--dusk alp-cover"><?php echo $img_tag( $f( 'cha_photo' ), 'Luxury chalet interior', $dp['cha'] ); ?></div></div>
		<div class="alp-chalet__body">
			<span class="alp-kicker"><?php echo esc_html( $f( 'cha_kicker' ) ); ?></span>
			<h2 class="alp-h2"><?php echo esc_html( $f( 'cha_title' ) ); ?> <em><?php echo esc_html( $f( 'cha_title_em' ) ); ?></em></h2>
			<p class="alp-sub"><?php echo esc_html( $f( 'cha_sub' ) ); ?></p>
			<ul class="alp-list">
				<?php foreach ( $cha as $it ) : ?>
					<li><b><?php echo wp_kses_post( $it['cha_l'] ); ?></b><span><?php echo esc_html( $it['cha_v'] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div></div></section>

	<!-- SHOWCASE 2 -->
	<section class="alp-showcase alp-showcase--flip alp-full">
		<div class="alp-showcase__media"><div class="alp-ph alp-ph--dusk alp-cover"><?php echo $img_tag( $f( 'sc2_photo' ), 'Altitude gastronomy', $dp['sc2'] ); ?></div></div>
		<div class="alp-showcase__body"><div>
			<span class="alp-kicker"><?php echo esc_html( $f( 'sc2_kicker' ) ); ?></span>
			<h2><?php echo esc_html( $f( 'sc2_title' ) ); ?></h2>
			<p><?php echo esc_html( $f( 'sc2_p1' ) ); ?></p>
			<p><?php echo esc_html( $f( 'sc2_p2' ) ); ?></p>
		</div></div>
	</section>

	<!-- QUOTE -->
	<section class="alp-quote alp-full">
		<div class="alp-ph alp-ph--snow alp-cover"><?php echo $img_tag( $f( 'quote_photo' ), 'Alpine panorama at dusk', $dp['quote'] ); ?></div>
		<div class="alp-quote__veil"></div>
		<div class="alp-quote__c">
			<p class="alp-quote__t">&ldquo;<?php echo esc_html( $f( 'quote_text' ) ); ?>&rdquo;</p>
			<div class="alp-quote__a"><?php echo esc_html( $f( 'quote_author' ) ); ?></div>
		</div>
	</section>

	<!-- FAQ -->
	<section class="alp-faq-sec"><div class="alp-wrap">
		<div class="alp-head--center">
			<span class="alp-kicker"><?php echo esc_html( $f( 'faq_kicker' ) ); ?></span>
			<h2 class="alp-h2"><?php echo esc_html( $f( 'faq_title' ) ); ?> <em><?php echo esc_html( $f( 'faq_title_em' ) ); ?></em></h2>
		</div>
		<div class="alp-faq">
			<?php foreach ( array_values( $faq ) as $i => $qa ) : ?>
				<details<?php echo $i === 0 ? ' open' : ''; ?>><summary><?php echo esc_html( $qa['faq_q'] ); ?></summary><p><?php echo esc_html( $qa['faq_a'] ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div></section>

	<!-- FORM -->
	<section id="plan" class="alp-plan alp-full">
		<div class="alp-plan__bg"><div class="alp-ph alp-cover"><?php echo $img_tag( $f( 'form_photo' ), 'Illuminated chalet at night', $dp['form'] ); ?></div></div>
		<div class="alp-plan__veil"></div>
		<div class="alp-plan__inner">
			<div class="alp-plan__l">
				<span class="alp-kicker"><?php echo esc_html( $f( 'form_kicker' ) ); ?></span>
				<h2><?php echo esc_html( $f( 'form_title' ) ); ?></h2>
				<p><?php echo esc_html( $f( 'form_intro' ) ); ?></p>
			</div>
			<div class="alp-form">
				<?php $embed = $f( 'form_embed' );
				if ( trim( $embed ) !== '' ) { echo $embed; } else { ?>
					<p style="font-size:16px;color:#79817F;margin:0 0 24px;line-height:1.7;">Share a few details and one of our alpine specialists will be in touch within 24 hours.</p>
					<a href="<?php echo esc_url( $f( 'form_fb_url', '/tailor-my-trip/' ) ); ?>" class="alp-btn alp-btn--dark"><?php echo esc_html( $f( 'form_fb_label' ) ); ?></a>
				<?php } ?>
			</div>
		</div>
	</section>

	</div>
	<?php
	$me = array();
	foreach ( $faq as $qa ) $me[] = array( '@type' => 'Question', 'name' => wp_strip_all_tags( $qa['faq_q'] ), 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $qa['faq_a'] ) ) );
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $me ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
	echo ob_get_clean();
}

/* ================================================================== *
 * MVP — Bibliothèque de modèles (moteur)
 * -----------------------------------------------------------------
 * Chaque page devient un « modèle » insérable via l'inséreur natif
 * de WordPress (Motifs). Zéro écran d'admin custom, zéro mise à jour
 * d'extension pour réutiliser : Béatrice insère un modèle, l'adapte,
 * et peut enregistrer SA version comme motif synchronisé (stocké en
 * base). L'admin dédié (menu, bibliothèque visuelle) viendra ensuite.
 * ================================================================== */

/* Définition centralisée des modèles fournis (source unique — demain
   alimentable depuis un JSON ou la base sans toucher au reste). */
function aav_lb_templates() {
	return array(
		'alsace' => array(
			'block'       => 'acf/aav-alsace',
			'title'       => 'AAV — Christmas in Alsace',
			'description' => 'Page complète « Noël en Alsace » (bordeaux/Playfair), prête à éditer.',
		),
		'paris' => array(
			'block'       => 'acf/aav-paris',
			'title'       => 'AAV — Christmas in Paris',
			'description' => 'Page complète « Noël à Paris » (bordeaux/Playfair), prête à éditer.',
		),
		'alps' => array(
			'block'       => 'acf/aav-alps',
			'title'       => 'AAV — Winter in the French Alps',
			'description' => 'Page complète « Hiver dans les Alpes » (bleu glacier), prête à éditer.',
		),
		'sustainability' => array(
			'block'       => 'acf/aav-sustainability',
			'title'       => 'AAV — Sustainability',
			'description' => 'Page complète « Sustainability / Our Commitment » (bordeaux/Playfair), prête à éditer.',
		),
		'little-black-book' => array(
			'block'       => 'acf/aav-little-black-book',
			'title'       => 'AAV — The Little Black Book',
			'description' => 'Page complète « The Little Black Book » (noir, bordeaux, or, Playfair), prête à éditer.',
		),
		'our-story' => array(
			'block'       => 'acf/aav-our-story',
			'title'       => 'AAV — Our Story (20 ans)',
			'description' => 'Page anniversaire complète : rail de reliure, chronologie, équipe, avis clients. Prête à éditer.',
		),
	);
}

add_action( 'init', function () {

	if ( ! function_exists( 'register_block_pattern' ) ) return;

	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category( 'aav-landing', array(
			'label' => __( 'AAV — Landing Pages', 'aav-lb' ),
		) );
	}

	foreach ( aav_lb_templates() as $slug => $tpl ) {
		$content = '<!-- wp:' . $tpl['block'] . ' {"name":"' . $tpl['block'] . '","mode":"preview"} /-->';
		register_block_pattern( 'aav-lb/' . $slug, array(
			'title'         => $tpl['title'],
			'description'   => $tpl['description'],
			'categories'    => array( 'aav-landing' ),
			'keywords'      => array( 'aav', 'landing', 'noel', 'christmas', $slug ),
			'content'       => $content,
			'viewportWidth' => 1400,
			'inserter'      => true,
		) );
	}
} );

/* ================================================================== *
 * LOT A — Photos par défaut robustes (résistantes au renommage)
 * -----------------------------------------------------------------
 * 1) aav_lb_url_to_id() : retrouve l'ID de médiathèque d'une URL, et
 *    met le résultat en cache. Une fois l'ID connu, l'image reste
 *    affichée même si le fichier est renommé plus tard (SEO).
 * 2) Les rendus utilisent cet ID pour les photos par défaut.
 * 3) acf/load_value : pré-remplit les champs image vides avec l'ID
 *    de la photo par défaut → à l'enregistrement, la page référence
 *    ses images par ID (matérialisées), plus par URL codée en dur.
 * ================================================================== */

/* URL de médiathèque -> ID de pièce jointe, avec cache persistant. */
function aav_lb_url_to_id( $url ) {
	if ( ! $url ) return 0;
	static $mem = array();
	if ( isset( $mem[ $url ] ) ) return $mem[ $url ];

	$cache = get_option( 'aav_lb_url_ids', array() );
	if ( is_array( $cache ) && isset( $cache[ $url ] ) ) {
		$mem[ $url ] = (int) $cache[ $url ];
		return $mem[ $url ];
	}

	$id = function_exists( 'attachment_url_to_postid' ) ? (int) attachment_url_to_postid( $url ) : 0;

	if ( $id > 0 ) {
		if ( ! is_array( $cache ) ) $cache = array();
		$cache[ $url ] = $id;
		update_option( 'aav_lb_url_ids', $cache, false );
	}
	$mem[ $url ] = $id;
	return $id;
}

/* Carte : clé de champ image (haut niveau) -> URL de la photo par défaut. */
function aav_lb_photo_defaults() {
	$P = 'https://www.aavluxurytravel.com/wp-content/uploads/2026/07/';
	return array(
		// Alsace
		'fld_hero_photo'   => $P . 'AdobeStock_277990274-1-scaled.jpeg',
		'fld_sc1_photo'    => $P . 'AdobeStock_1888948884_Editorial_Use_Only-1-scaled.jpeg',
		'fld_sc2_photo'    => $P . 'AdobeStock_624497591_Editorial_Use_Only-scaled.jpeg',
		'fld_stay_photo'   => $P . 'AdobeStock_567757701_Editorial_Use_Only-scaled.jpeg',
		'fld_quote_photo'  => $P . 'AdobeStock_46822472-scaled.jpeg',
		'fld_form_photo'   => $P . 'AdobeStock_467246685-2-scaled.jpeg',
		// Paris
		'pfld_hero_photo'  => $P . 'AdobeStock_239094391-scaled.jpeg',
		'pfld_band_photo'  => $P . 'AdobeStock_239350641-scaled.jpeg',
		'pfld_sc1_photo'   => $P . 'AdobeStock_205955056-scaled.jpeg',
		'pfld_sc2_photo'   => $P . 'AdobeStock_726495710_Editorial_Use_Only-1-scaled.jpeg',
		// Alpes
		'afld_hero_photo'  => $P . 'AdobeStock_326486950-scaled.jpeg',
		'afld_sc1_photo'   => $P . 'AdobeStock_1878173023-1.jpeg',
		'afld_cha_photo'   => $P . 'AdobeStock_1041650015-1-scaled.jpeg',
		'afld_sc2_photo'   => $P . 'AdobeStock_298961285-scaled.jpeg',
		'afld_quote_photo' => $P . 'AdobeStock_498019979-1-scaled.jpeg',
		'afld_form_photo'  => $P . 'AdobeStock_407482210-1-scaled.jpeg',
		// Sustainability
		'sfld_hero_photo'  => $P . 'AdobeStock_513153814-1-scaled.jpeg',
		'sfld_fon_photo'   => $P . 'fondation-patrimoine.jpg',
		'sfld_tre_photo'   => $P . 'Tree-Nation-Logo.jpg',
		// Our Story
		'ofld_ap_photo'    => $P . 'Eric-01.png',
	);
}

/* Champ image vide -> ID de la photo par défaut (matérialisation). */
add_filter( 'acf/load_value', function ( $value, $post_id, $field ) {
	if ( ! empty( $value ) ) return $value;
	if ( empty( $field['key'] ) ) return $value;

	$map = aav_lb_photo_defaults();
	if ( ! isset( $map[ $field['key'] ] ) ) return $value;

	$id = aav_lb_url_to_id( $map[ $field['key'] ] );
	return $id > 0 ? $id : $value;
}, 10, 3 );

/* ================================================================== *
 * LOT B — Bloc « Landing sur-mesure » (sections composables)
 * -----------------------------------------------------------------
 * Un seul bloc, un champ Flexible Content : Béatrice ajoute, réordonne
 * et supprime des sections choisies dans une bibliothèque (Hero, Split,
 * Mosaïque, Bandes, Itinéraire, Séjour, Citation, FAQ, Formulaire),
 * pour composer des pages entièrement nouvelles — sans code.
 * ================================================================== */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) return;
	acf_register_block_type( array(
		'name'            => 'aav-builder',
		'title'           => __( 'AAV — Landing sur-mesure', 'aav-lb' ),
		'description'     => __( 'Composez une page en empilant des sections (Hero, Split, Mosaïque, FAQ…).', 'aav-lb' ),
		'category'        => 'formatting',
		'icon'            => 'layout',
		'keywords'        => array( 'aav', 'landing', 'builder', 'sections', 'sur-mesure' ),
		'mode'            => 'edit',
		'api_version'       => 3,
		'acf_block_version' => 3,
		'render_preview'    => false,
		'supports'        => array( 'align' => false, 'multiple' => true, 'jsx' => true, 'mode' => false ),
		'render_callback' => 'aav_lb_render_builder',
	) );
} );

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$T   = function ( $key, $label, $type = 'text', $default = '', $instr = '' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => $type, 'default_value' => $default, 'instructions' => $instr );
	};
	$img = function ( $key, $label = 'Photo' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all', 'instructions' => 'Vide = dégradé de couleur.' );
	};
	$amb = function ( $key ) {
		return array( 'key' => $key, 'label' => 'Ambiance de la photo', 'name' => substr( $key, 5 ), 'type' => 'select', 'default_value' => '', 'choices' => array(
			'' => 'Bordeaux (défaut)', 'lb-ph--warm' => 'Chaud', 'lb-ph--night' => 'Nuit', 'lb-ph--snow' => 'Neige', 'lb-ph--dusk' => 'Crépuscule',
		) );
	};
	$rep = function ( $key, $label, $btn, $subs, $layout = 'block' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => 'repeater', 'layout' => $layout, 'button_label' => $btn, 'sub_fields' => $subs );
	};

	$layouts = array(

		'hero' => array( 'key' => 'blay_hero', 'name' => 'hero', 'label' => 'Hero (plein écran)', 'display' => 'block', 'sub_fields' => array(
			$img( 'bfld_h_photo' ),
			$T( 'bfld_h_kicker', 'Sur-titre' ),
			$T( 'bfld_h_t1', 'Titre — 1re ligne' ),
			$T( 'bfld_h_t2', 'Titre — 2e ligne (italique, facultatif)' ),
			$T( 'bfld_h_sub', 'Accroche', 'textarea' ),
			$T( 'bfld_h_cta1', 'Bouton 1 — libellé' ),
			$T( 'bfld_h_cta1u', 'Bouton 1 — lien', 'text', '#' ),
			$T( 'bfld_h_cta2', 'Bouton 2 — libellé (facultatif)' ),
			$T( 'bfld_h_cta2u', 'Bouton 2 — lien', 'text', '#' ),
			$rep( 'bfld_h_stats', 'Chiffres (facultatif)', 'Ajouter un chiffre', array(
				$T( 'bfld_h_sv', 'Valeur' ), $T( 'bfld_h_sl', 'Libellé' ),
			), 'table' ),
		) ),

		'intro' => array( 'key' => 'blay_intro', 'name' => 'intro', 'label' => 'Intro / Manifeste', 'display' => 'block', 'sub_fields' => array(
			$T( 'bfld_i_kicker', 'Sur-titre' ),
			$T( 'bfld_i_num', 'Grand numéro (filigrane)', 'text', '01' ),
			$T( 'bfld_i_lead', 'Accroche (mot entre *astérisques* = italique)', 'textarea' ),
			$T( 'bfld_i_body', 'Paragraphe', 'textarea' ),
			$T( 'bfld_i_body2', 'Second paragraphe (facultatif)', 'textarea' ),
		) ),

		'showcase' => array( 'key' => 'blay_sc', 'name' => 'showcase', 'label' => 'Split photo / texte', 'display' => 'block', 'sub_fields' => array(
			$img( 'bfld_s_photo' ),
			$amb( 'bfld_s_amb' ),
			array( 'key' => 'bfld_s_flip', 'label' => 'Photo à droite', 'name' => 'flip', 'type' => 'true_false', 'ui' => 1 ),
			$T( 'bfld_s_kicker', 'Sur-titre' ),
			$T( 'bfld_s_title', 'Titre' ),
			$T( 'bfld_s_titleem', 'Titre — suite en italique (facultatif)' ),
			$T( 'bfld_s_p1', 'Paragraphe 1', 'textarea' ),
			$T( 'bfld_s_p2', 'Paragraphe 2 (facultatif)', 'textarea' ),
		) ),

		'mosaic' => array( 'key' => 'blay_mo', 'name' => 'mosaic', 'label' => 'Mosaïque (cartes)', 'display' => 'block', 'sub_fields' => array(
			$T( 'bfld_m_kicker', 'Sur-titre' ),
			$T( 'bfld_m_title', 'Titre' ),
			$T( 'bfld_m_titleem', 'Titre — italique (facultatif)' ),
			$T( 'bfld_m_intro', 'Intro (facultatif)', 'textarea' ),
			$rep( 'bfld_m_items', 'Cartes (la 1re est plus grande)', 'Ajouter une carte', array(
				$img( 'bfld_m_photo' ), $amb( 'bfld_m_amb' ),
				$T( 'bfld_m_alt', 'Étiquette (petit texte)' ),
				$T( 'bfld_m_h', 'Titre' ),
				$T( 'bfld_m_d', 'Description', 'textarea' ),
			) ),
		) ),

		'strips' => array( 'key' => 'blay_st', 'name' => 'strips', 'label' => 'Bandes numérotées', 'display' => 'block', 'sub_fields' => array(
			$T( 'bfld_st_kicker', 'Sur-titre' ),
			$T( 'bfld_st_title', 'Titre' ),
			$T( 'bfld_st_titleem', 'Titre — italique (facultatif)' ),
			$rep( 'bfld_st_items', 'Bandes', 'Ajouter une bande', array(
				$T( 'bfld_st_n', 'Numéro (I, II…)' ),
				$T( 'bfld_st_t', 'Titre' ),
				$T( 'bfld_st_d', 'Description', 'textarea' ),
			) ),
		) ),

		'itinerary' => array( 'key' => 'blay_it', 'name' => 'itinerary', 'label' => 'Itinéraire (jours)', 'display' => 'block', 'sub_fields' => array(
			$T( 'bfld_it_kicker', 'Sur-titre' ),
			$T( 'bfld_it_title', 'Titre' ),
			$T( 'bfld_it_titleem', 'Titre — italique (facultatif)' ),
			$rep( 'bfld_it_days', 'Jours', 'Ajouter un jour', array(
				$T( 'bfld_it_n', 'Numéro' ),
				$T( 'bfld_it_t', 'Titre du jour' ),
				$T( 'bfld_it_d', 'Description', 'textarea' ),
			) ),
		) ),

		'stay' => array( 'key' => 'blay_sy', 'name' => 'stay', 'label' => 'Séjour (photo + liste)', 'display' => 'block', 'sub_fields' => array(
			$img( 'bfld_sy_photo' ), $amb( 'bfld_sy_amb' ),
			$T( 'bfld_sy_kicker', 'Sur-titre' ),
			$T( 'bfld_sy_title', 'Titre' ),
			$T( 'bfld_sy_titleem', 'Titre — italique (facultatif)' ),
			$T( 'bfld_sy_intro', 'Intro', 'textarea' ),
			$rep( 'bfld_sy_items', 'Lignes', 'Ajouter une ligne', array(
				$T( 'bfld_sy_l', 'Prestation' ), $T( 'bfld_sy_v', 'Mention (à droite)' ),
			), 'table' ),
		) ),

		'quote' => array( 'key' => 'blay_q', 'name' => 'quote', 'label' => 'Citation (pleine largeur)', 'display' => 'block', 'sub_fields' => array(
			$img( 'bfld_q_photo', 'Photo de fond' ), $amb( 'bfld_q_amb' ),
			$T( 'bfld_q_text', 'Citation', 'textarea' ),
			$T( 'bfld_q_author', 'Auteur' ),
		) ),

		'faq' => array( 'key' => 'blay_f', 'name' => 'faq', 'label' => 'FAQ', 'display' => 'block', 'sub_fields' => array(
			$T( 'bfld_f_kicker', 'Sur-titre' ),
			$T( 'bfld_f_title', 'Titre' ),
			$T( 'bfld_f_titleem', 'Titre — italique (facultatif)' ),
			$rep( 'bfld_f_items', 'Questions', 'Ajouter une question', array(
				$T( 'bfld_f_q', 'Question' ), $T( 'bfld_f_a', 'Réponse', 'textarea' ),
			) ),
		) ),

		'cta' => array( 'key' => 'blay_c', 'name' => 'cta', 'label' => 'Formulaire / Appel à l’action', 'display' => 'block', 'sub_fields' => array(
			$img( 'bfld_c_photo', 'Photo de fond' ),
			$T( 'bfld_c_kicker', 'Sur-titre' ),
			$T( 'bfld_c_title', 'Titre' ),
			$T( 'bfld_c_titleem', 'Titre — italique (facultatif)' ),
			$T( 'bfld_c_intro', 'Intro', 'textarea' ),
			$T( 'bfld_c_embed', 'Code d’intégration HubSpot (facultatif)', 'textarea', '', 'Si vide, un bouton de secours est affiché.' ),
			$T( 'bfld_c_fbl', 'Bouton de secours — libellé', 'text', 'Plan Your Journey' ),
			$T( 'bfld_c_fbu', 'Bouton de secours — lien', 'text', '/tailor-my-trip/' ),
		) ),
	);

	acf_add_local_field_group( array(
		'key'    => 'group_aav_lb_builder',
		'title'  => 'AAV — Landing sur-mesure',
		'fields' => array(
			array(
				'key'          => 'bfld_sections',
				'label'        => 'Sections de la page',
				'name'         => 'lb_sections',
				'type'         => 'flexible_content',
				'button_label' => 'Ajouter une section',
				'layouts'      => $layouts,
			),
		),
		'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/aav-builder' ) ) ),
	) );
} );

function aav_lb_render_builder( $block, $content = '', $is_preview = false ) {

	if ( $is_preview || is_admin() ) {
		aav_lb_editor_placeholder( 'AAV — Landing sur-mesure' );
		return;
	}

	/* Surcharge HTML du modele : si presente, elle remplace le rendu dynamique */
	if ( aav_lb_maybe_render_override( 'lb' ) ) return;

	$amb_ok = array( '', 'lb-ph--warm', 'lb-ph--night', 'lb-ph--snow', 'lb-ph--dusk' );
	$amb = function ( $v ) use ( $amb_ok ) { return in_array( $v, $amb_ok, true ) ? $v : ''; };
	$img = function ( $id, $alt ) {
		$u = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
		return $u ? '<img src="' . esc_url( $u ) . '" alt="' . esc_attr( $alt ) . '" onerror="this.style.display=\'none\'">' : '';
	};
	$em  = function ( $t ) { return preg_replace( '/\*(.+?)\*/', '<em>$1</em>', esc_html( $t ) ); };
	$te  = function ( $t, $e ) { $o = esc_html( $t ); if ( trim( (string) $e ) !== '' ) $o .= ' <em>' . esc_html( $e ) . '</em>'; return $o; };

	$faq_all = array();
	$css = aav_lb_css( 'lb' );

	ob_start();
	if ( $css ) echo '<style>' . $css . '</style>';
	echo '<div class="lb-root">';

	if ( have_rows( 'lb_sections' ) ) :
		while ( have_rows( 'lb_sections' ) ) : the_row();
			$L = get_row_layout();

			/* ---------- HERO ---------- */
			if ( 'hero' === $L ) :
				$t1 = get_sub_field( 'title_1' ); $t2 = get_sub_field( 'title_2' );
				$title = esc_html( $t1 ); if ( trim( (string) $t2 ) !== '' ) $title .= '<br><em>' . esc_html( $t2 ) . '</em>';
				?>
				<section class="lb-hero lb-full">
					<div class="lb-ph lb-ph--night lb-cover"><?php echo $img( get_sub_field( 'photo' ), 'Hero' ); ?></div>
					<div class="lb-hero__veil"></div><div class="lb-hero__veil2"></div>
					<div class="lb-hero__inner"><div class="lb-hero__grid">
						<div>
							<?php if ( get_sub_field( 'kicker' ) ) : ?><span class="lb-kicker"><?php echo esc_html( get_sub_field( 'kicker' ) ); ?></span><?php endif; ?>
							<h1 class="lb-hero__title"><?php echo $title; ?></h1>
							<?php if ( get_sub_field( 'sub' ) ) : ?><p class="lb-hero__sub"><?php echo esc_html( get_sub_field( 'sub' ) ); ?></p><?php endif; ?>
							<div class="lb-hero__cta">
								<?php if ( get_sub_field( 'cta1' ) ) : ?><a href="<?php echo esc_url( get_sub_field( 'cta1_url' ) ?: '#' ); ?>" class="lb-btn lb-btn--champ"><?php echo esc_html( get_sub_field( 'cta1' ) ); ?></a><?php endif; ?>
								<?php if ( get_sub_field( 'cta2' ) ) : ?><a href="<?php echo esc_url( get_sub_field( 'cta2_url' ) ?: '#' ); ?>" class="lb-btn lb-btn--wire"><?php echo esc_html( get_sub_field( 'cta2' ) ); ?></a><?php endif; ?>
							</div>
						</div>
						<?php if ( have_rows( 'stats' ) ) : ?>
							<div class="lb-stats">
								<?php while ( have_rows( 'stats' ) ) : the_row(); ?>
									<div class="lb-stat"><strong><?php echo wp_kses_post( get_sub_field( 'v' ) ); ?></strong><span><?php echo wp_kses_post( get_sub_field( 'l' ) ); ?></span></div>
								<?php endwhile; ?>
							</div>
						<?php endif; ?>
					</div></div>
				</section>
				<?php

			/* ---------- INTRO ---------- */
			elseif ( 'intro' === $L ) : ?>
				<section class="lb-manifesto"><div class="lb-wrap"><div class="lb-manifesto__grid">
					<div class="lb-numeral"><?php echo esc_html( get_sub_field( 'numeral' ) ?: '01' ); ?></div>
					<div>
						<?php if ( get_sub_field( 'kicker' ) ) : ?><span class="lb-kicker lb-kicker--wine"><?php echo esc_html( get_sub_field( 'kicker' ) ); ?></span><?php endif; ?>
						<?php if ( get_sub_field( 'lead' ) ) : ?><p class="lb-lead"><?php echo $em( get_sub_field( 'lead' ) ); ?></p><?php endif; ?>
						<?php if ( get_sub_field( 'body' ) ) : ?><p class="lb-body" style="margin-bottom:16px;"><?php echo esc_html( get_sub_field( 'body' ) ); ?></p><?php endif; ?>
						<?php if ( get_sub_field( 'body2' ) ) : ?><p class="lb-body"><?php echo esc_html( get_sub_field( 'body2' ) ); ?></p><?php endif; ?>
					</div>
				</div></div></section>
				<?php

			/* ---------- SHOWCASE ---------- */
			elseif ( 'showcase' === $L ) :
				$flip = get_sub_field( 'flip' ) ? ' lb-showcase--flip' : ''; ?>
				<section class="lb-showcase<?php echo $flip; ?> lb-full">
					<div class="lb-showcase__media"><div class="lb-ph <?php echo esc_attr( $amb( get_sub_field( 'ambiance' ) ) ); ?> lb-cover"><?php echo $img( get_sub_field( 'photo' ), '' ); ?></div></div>
					<div class="lb-showcase__body"><div>
						<?php if ( get_sub_field( 'kicker' ) ) : ?><span class="lb-kicker"><?php echo esc_html( get_sub_field( 'kicker' ) ); ?></span><?php endif; ?>
						<h2><?php echo $te( get_sub_field( 'title' ), get_sub_field( 'title_em' ) ); ?></h2>
						<?php if ( get_sub_field( 'p1' ) ) : ?><p><?php echo esc_html( get_sub_field( 'p1' ) ); ?></p><?php endif; ?>
						<?php if ( get_sub_field( 'p2' ) ) : ?><p><?php echo esc_html( get_sub_field( 'p2' ) ); ?></p><?php endif; ?>
					</div></div>
				</section>
				<?php

			/* ---------- MOSAIC ---------- */
			elseif ( 'mosaic' === $L ) : ?>
				<section class="lb-places"><div class="lb-wrap">
					<div class="lb-head">
						<?php if ( get_sub_field( 'kicker' ) ) : ?><span class="lb-kicker lb-kicker--wine"><?php echo esc_html( get_sub_field( 'kicker' ) ); ?></span><?php endif; ?>
						<h2 class="lb-h2"><?php echo $te( get_sub_field( 'title' ), get_sub_field( 'title_em' ) ); ?></h2>
						<?php if ( get_sub_field( 'intro' ) ) : ?><p class="lb-body" style="margin-top:20px;"><?php echo esc_html( get_sub_field( 'intro' ) ); ?></p><?php endif; ?>
					</div>
					<div class="lb-grid">
						<?php $i = 0; if ( have_rows( 'items' ) ) : while ( have_rows( 'items' ) ) : the_row();
							$tall = 0 === $i ? ' lb-p--tall' : ''; $i++; ?>
							<div class="lb-p<?php echo $tall; ?>">
								<div class="lb-ph <?php echo esc_attr( $amb( get_sub_field( 'ambiance' ) ) ); ?> lb-cover"><?php echo $img( get_sub_field( 'photo' ), get_sub_field( 'title' ) ); ?></div>
								<div class="lb-p__veil"></div>
								<div class="lb-p__c">
									<?php if ( get_sub_field( 'alt' ) ) : ?><span class="lb-p__alt"><?php echo esc_html( get_sub_field( 'alt' ) ); ?></span><?php endif; ?>
									<h3><?php echo esc_html( get_sub_field( 'title' ) ); ?></h3>
									<?php if ( get_sub_field( 'desc' ) ) : ?><p><?php echo esc_html( get_sub_field( 'desc' ) ); ?></p><?php endif; ?>
								</div>
							</div>
						<?php endwhile; endif; ?>
					</div>
				</div></section>
				<?php

			/* ---------- STRIPS ---------- */
			elseif ( 'strips' === $L ) : ?>
				<section class="lb-moments"><div class="lb-wrap">
					<div class="lb-head--center" style="max-width:660px;">
						<?php if ( get_sub_field( 'kicker' ) ) : ?><span class="lb-kicker"><?php echo esc_html( get_sub_field( 'kicker' ) ); ?></span><?php endif; ?>
						<h2 class="lb-h2 lb-h2--light"><?php echo $te( get_sub_field( 'title' ), get_sub_field( 'title_em' ) ); ?></h2>
					</div>
					<div>
						<?php if ( have_rows( 'items' ) ) : while ( have_rows( 'items' ) ) : the_row(); ?>
							<div class="lb-strip"><div class="lb-strip__n"><?php echo esc_html( get_sub_field( 'n' ) ); ?></div><div class="lb-strip__t"><?php echo esc_html( get_sub_field( 'title' ) ); ?></div><div class="lb-strip__d"><?php echo esc_html( get_sub_field( 'desc' ) ); ?></div></div>
						<?php endwhile; endif; ?>
					</div>
				</div></section>
				<?php

			/* ---------- ITINERARY ---------- */
			elseif ( 'itinerary' === $L ) : ?>
				<section class="lb-itin"><div class="lb-wrap">
					<div class="lb-head--center" style="max-width:640px;">
						<?php if ( get_sub_field( 'kicker' ) ) : ?><span class="lb-kicker lb-kicker--wine"><?php echo esc_html( get_sub_field( 'kicker' ) ); ?></span><?php endif; ?>
						<h2 class="lb-h2"><?php echo $te( get_sub_field( 'title' ), get_sub_field( 'title_em' ) ); ?></h2>
						<hr class="lb-rule" />
					</div>
					<div class="lb-days">
						<?php if ( have_rows( 'days' ) ) : while ( have_rows( 'days' ) ) : the_row(); ?>
							<div class="lb-day"><div class="lb-day__n"><?php echo esc_html( get_sub_field( 'n' ) ); ?><span>Day</span></div><div class="lb-day__c"><h3><?php echo esc_html( get_sub_field( 'title' ) ); ?></h3><p><?php echo esc_html( get_sub_field( 'desc' ) ); ?></p></div></div>
						<?php endwhile; endif; ?>
					</div>
				</div></section>
				<?php

			/* ---------- STAY ---------- */
			elseif ( 'stay' === $L ) : ?>
				<section class="lb-stay"><div class="lb-wrap" style="padding:0;"><div class="lb-stay__grid">
					<div class="lb-stay__media"><div class="lb-ph <?php echo esc_attr( $amb( get_sub_field( 'ambiance' ) ) ); ?> lb-cover"><?php echo $img( get_sub_field( 'photo' ), '' ); ?></div></div>
					<div class="lb-stay__body">
						<?php if ( get_sub_field( 'kicker' ) ) : ?><span class="lb-kicker lb-kicker--wine"><?php echo esc_html( get_sub_field( 'kicker' ) ); ?></span><?php endif; ?>
						<h2 class="lb-h2" style="font-size:clamp(26px,3.4vw,38px);"><?php echo $te( get_sub_field( 'title' ), get_sub_field( 'title_em' ) ); ?></h2>
						<?php if ( get_sub_field( 'intro' ) ) : ?><p class="lb-body" style="margin-top:18px;"><?php echo esc_html( get_sub_field( 'intro' ) ); ?></p><?php endif; ?>
						<ul class="lb-list">
							<?php if ( have_rows( 'items' ) ) : while ( have_rows( 'items' ) ) : the_row(); ?>
								<li><b><?php echo esc_html( get_sub_field( 'label' ) ); ?></b><span><?php echo esc_html( get_sub_field( 'value' ) ); ?></span></li>
							<?php endwhile; endif; ?>
						</ul>
					</div>
				</div></div></section>
				<?php

			/* ---------- QUOTE ---------- */
			elseif ( 'quote' === $L ) : ?>
				<section class="lb-quote lb-full">
					<div class="lb-ph <?php echo esc_attr( $amb( get_sub_field( 'ambiance' ) ) ); ?> lb-cover"><?php echo $img( get_sub_field( 'photo' ), '' ); ?></div>
					<div class="lb-quote__veil"></div>
					<div class="lb-quote__c">
						<p class="lb-quote__t">&ldquo;<?php echo esc_html( get_sub_field( 'text' ) ); ?>&rdquo;</p>
						<?php if ( get_sub_field( 'author' ) ) : ?><div class="lb-quote__a"><?php echo esc_html( get_sub_field( 'author' ) ); ?></div><?php endif; ?>
					</div>
				</section>
				<?php

			/* ---------- FAQ ---------- */
			elseif ( 'faq' === $L ) : ?>
				<section class="lb-faq-sec"><div class="lb-wrap">
					<div class="lb-head--center" style="max-width:620px;">
						<?php if ( get_sub_field( 'kicker' ) ) : ?><span class="lb-kicker lb-kicker--wine"><?php echo esc_html( get_sub_field( 'kicker' ) ); ?></span><?php endif; ?>
						<h2 class="lb-h2"><?php echo $te( get_sub_field( 'title' ), get_sub_field( 'title_em' ) ); ?></h2>
					</div>
					<div class="lb-faq">
						<?php $j = 0; if ( have_rows( 'items' ) ) : while ( have_rows( 'items' ) ) : the_row();
							$q = get_sub_field( 'q' ); $a = get_sub_field( 'a' );
							$faq_all[] = array( 'q' => $q, 'a' => $a ); ?>
							<details<?php echo 0 === $j ? ' open' : ''; ?>><summary><?php echo esc_html( $q ); ?></summary><p><?php echo esc_html( $a ); ?></p></details>
						<?php $j++; endwhile; endif; ?>
					</div>
				</div></section>
				<?php

			/* ---------- CTA / FORM ---------- */
			elseif ( 'cta' === $L ) : ?>
				<section class="lb-plan lb-full">
					<div class="lb-ph lb-ph--night lb-cover"><?php echo $img( get_sub_field( 'photo' ), '' ); ?></div>
					<div class="lb-plan__veil"></div>
					<div class="lb-plan__inner">
						<div class="lb-plan__l">
							<?php if ( get_sub_field( 'kicker' ) ) : ?><span class="lb-kicker"><?php echo esc_html( get_sub_field( 'kicker' ) ); ?></span><?php endif; ?>
							<h2><?php echo $te( get_sub_field( 'title' ), get_sub_field( 'title_em' ) ); ?></h2>
							<?php if ( get_sub_field( 'intro' ) ) : ?><p><?php echo esc_html( get_sub_field( 'intro' ) ); ?></p><?php endif; ?>
						</div>
						<div class="lb-form">
							<?php $embed = get_sub_field( 'embed' );
							if ( trim( (string) $embed ) !== '' ) { echo $embed; } else { ?>
								<div style="text-align:center;">
									<p style="font-size:16px;color:#8A8078;margin:0 0 24px;line-height:1.85;">Share a few details and we will be in touch within 24 hours.</p>
									<a href="<?php echo esc_url( get_sub_field( 'fb_url' ) ?: '/tailor-my-trip/' ); ?>" class="lb-btn lb-btn--wine"><?php echo esc_html( get_sub_field( 'fb_label' ) ?: 'Plan Your Journey' ); ?></a>
								</div>
							<?php } ?>
						</div>
					</div>
				</section>
				<?php

			endif;
		endwhile;
	else : ?>
		<div style="padding:60px 24px;text-align:center;color:#8A8078;font-family:Georgia,serif;">
			<em>Page sur-mesure — ajoutez des sections dans le panneau de droite.</em>
		</div>
	<?php endif;

	echo '</div>';

	if ( ! empty( $faq_all ) ) {
		$me = array();
		foreach ( $faq_all as $qa ) $me[] = array( '@type' => 'Question', 'name' => wp_strip_all_tags( $qa['q'] ), 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $qa['a'] ) ) );
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $me ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
	}

	echo ob_get_clean();
}

/* ================================================================== *
 * BLOC SUSTAINABILITY — page « Our Commitment / Sustainability »
 * -----------------------------------------------------------------
 * Conventions identiques aux blocs Paris / Alpes :
 *   - clés de champs : sfld_… / onglets : stab_…  (name = substr($key,5))
 *   - répéteurs sans valeur par défaut → repli sur tableaux $def_* au rendu
 *   - photos : ID de médiathèque, repli URL via aav_lb_url_to_id()
 * ================================================================== */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) return;
	acf_register_block_type( array(
		'name'            => 'aav-sustainability',
		'title'           => __( 'AAV — Page : Sustainability', 'aav-lb' ),
		'description'     => __( 'Éditable : textes, photos, chiffres et liens. Structure figée.', 'aav-lb' ),
		'category'        => 'formatting',
		'icon'            => 'palmtree',
		'keywords'        => array( 'aav', 'sustainability', 'patrimoine', 'engagement', 'rse' ),
		'mode'            => 'edit',
		'api_version'       => 3,
		'acf_block_version' => 3,
		'render_preview'    => false,
		'supports'        => array( 'align' => false, 'multiple' => false, 'jsx' => true, 'mode' => false ),
		'render_callback' => 'aav_lb_render_sustainability',
	) );
} );

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$T = function ( $key, $label, $default = '', $type = 'text', $instr = '' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => $type, 'default_value' => $default, 'instructions' => $instr );
	};
	$tab = function ( $key, $label ) { return array( 'key' => $key, 'label' => $label, 'type' => 'accordion', 'open' => 0, 'multi_expand' => 1 ); };
	$img = function ( $key, $label, $instr = 'Vide = la photo actuelle de la page reste affichée.' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all', 'instructions' => $instr );
	};

	$f = array();

	/* ---- Héro ---- */
	$f[] = $tab( 'stab_hero', 'Héro' );
	$f[] = $T( 'sfld_hero_video', 'Vidéo de fond (URL .mp4)', 'https://www.aavluxurytravel.com/wp-content/uploads/2026/07/Sustainability-AAV-V1.mp4', 'text', 'MP4 H.264, 1080p, boucle 10–20 s, sans son, idéalement moins de 10 Mo.' );
	$f[] = $img( 'sfld_hero_photo', 'Image de fond', 'Affichée pendant le chargement de la vidéo et en secours.' );
	$f[] = $T( 'sfld_hero_title', 'Titre', 'The art of living' );
	$f[] = $T( 'sfld_hero_title_em', 'Titre — italique doré', 'is an art of keeping' );
	$f[] = $T( 'sfld_hero_sub', 'Accroche', 'An art de vivre is not merely enjoyed; it is maintained. The châteaux and ateliers of France, the vineyards of Italy, the quintas of Portugal — each endures through the patience of those who hold them. For twenty years we have been their guests, and their contributors.', 'textarea' );

	/* ---- I. Engagement ---- */
	$f[] = $tab( 'stab_com', 'I — Engagement' );
	$f[] = $T( 'sfld_com_lb', 'Libellé du chapitre', 'Our commitment' );
	$f[] = $T( 'sfld_com_st', 'Grande phrase', 'The art of living is, above all,', 'textarea' );
	$f[] = $T( 'sfld_com_st_em', 'Grande phrase — partie italique', 'an act of preservation' );
	$f[] = $T( 'sfld_com_p1', 'Colonne gauche — paragraphe 1', 'The art of living is not an abstraction. It lives in a stonemason\'s hand, a vigneron\'s patience, a family that has kept the same roof above the same walls for three centuries. None of it survives on its own.', 'textarea' );
	$f[] = $T( 'sfld_com_p2', 'Colonne gauche — paragraphe 2', 'France is the heart of what we do and where most of our journeys unfold. The same conviction guides us wherever we travel — across England, Spain, Portugal, Italy and Switzerland. As a house that opens these doors, our responsibility is to contribute to what keeps them open.', 'textarea' );
	$f[] = $T( 'sfld_com_p3', 'Colonne droite — paragraphe 1', 'Much of it is simply how we have always worked. In every country we travel, the guides, drivers, hoteliers, château owners and artisans who shape your journey are local, independent, and engaged directly. Many have been part of the AAV story for the better part of two decades.', 'textarea' );
	$f[] = $T( 'sfld_com_p4', 'Colonne droite — paragraphe 2', 'Every journey we design sends its value to the people and places that create it. That, more than any label, is what we stand behind.', 'textarea' );

	/* ---- II. Fondation du Patrimoine ---- */
	$f[] = $tab( 'stab_fon', 'II — Fondation' );
	$f[] = $img( 'sfld_fon_photo', 'Photo' );
	$f[] = $T( 'sfld_fon_lb', 'Libellé du chapitre', 'Fondation du Patrimoine' );
	$f[] = $T( 'sfld_fon_title', 'Titre', 'Guardians of' );
	$f[] = $T( 'sfld_fon_title_em', 'Titre — italique doré', 'French heritage' );
	$f[] = $T( 'sfld_fon_lead', 'Chapeau', 'The monuments at the heart of our journeys endure because each generation chooses to protect them. In France, where most of our travels lead, we are proud to count ourselves among those who do.', 'textarea' );
	$f[] = $T( 'sfld_fon_p1', 'Paragraphe 1', 'We support the Fondation du Patrimoine, whose mission is to safeguard French heritage. For twenty-five years it has helped the custodians of historical landmarks find the private and public funding needed to restore them to their full grandeur.', 'textarea' );
	$f[] = $T( 'sfld_fon_p2', 'Paragraphe 2', 'Best known for the Notre-Dame de Paris campaign, the Fondation devotes most of its work to quieter treasures — village churches, windmills, historic workshops — and to landscapes such as the tadpole trees of the Marais Poitevin. These are precisely the places we love to show you.', 'textarea' );
	$f[] = array( 'key' => 'sfld_fon_figs', 'label' => 'Chiffres', 'name' => 'fon_figs', 'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Ajouter un chiffre', 'instructions' => 'Vide = garder les chiffres par défaut.', 'sub_fields' => array(
		$T( 'sfld_fon_fn', 'Chiffre', '' ), $T( 'sfld_fon_fl', 'Libellé', '' ),
	) );
	$f[] = $T( 'sfld_fon_link_l', 'Lien — libellé', 'Visit the Fondation →' );
	$f[] = $T( 'sfld_fon_link_u', 'Lien — URL', 'https://www.fondation-patrimoine.org/' );

	/* ---- III. La façon dont nous voyageons ---- */
	$f[] = $tab( 'stab_way', 'III — Nos choix' );
	$f[] = $T( 'sfld_way_lb', 'Libellé du chapitre', 'The way we travel' );
	$f[] = $T( 'sfld_way_title', 'Titre', 'Considered' );
	$f[] = $T( 'sfld_way_title_em', 'Titre — italique doré', 'at every turn' );
	$f[] = $T( 'sfld_way_intro', 'Introduction', 'The character of a journey is decided in its details. Whichever country you travel with us, these are the choices we make instinctively — and that you will recognise in any itinerary we write for you.', 'textarea' );
	$f[] = array( 'key' => 'sfld_way_items', 'label' => 'Nos choix', 'name' => 'way_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un choix', 'instructions' => 'Vide = garder les 6 choix par défaut.', 'sub_fields' => array(
		$T( 'sfld_way_h', 'Titre', '' ), $T( 'sfld_way_d', 'Description', '', 'textarea' ),
	) );

	/* ---- IV. Tree-Nation ---- */
	$f[] = $tab( 'stab_tre', 'IV — Tree-Nation' );
	$f[] = $img( 'sfld_tre_photo', 'Photo' );
	$f[] = $T( 'sfld_tre_lb', 'Libellé du chapitre', 'Tree-Nation' );
	$f[] = $T( 'sfld_tre_title', 'Titre', 'Planting for' );
	$f[] = $T( 'sfld_tre_title_em', 'Titre — italique doré', 'the next century' );
	$f[] = $T( 'sfld_tre_lead', 'Chapeau', 'Each year we fund the planting of 10,250 trees through Tree-Nation, an organisation that develops and monitors reforestation projects alongside local communities.', 'textarea' );
	$f[] = $T( 'sfld_tre_p1', 'Paragraphe 1', 'Every project is reviewed before it can receive funding, then followed through on-site audits and satellite monitoring to confirm the trees are planted and cared for. Among them is a French initiative converting farmland into agroforestry — cultivating trees and crops on the same land, enriching biodiversity while reducing what the soil is asked to bear.', 'textarea' );
	$f[] = $T( 'sfld_tre_p2', 'Paragraphe 2', 'We support this work simply because it deserves support — a contribution to landscapes that will outlast us all.', 'textarea' );
	$f[] = array( 'key' => 'sfld_tre_figs', 'label' => 'Chiffres', 'name' => 'tre_figs', 'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Ajouter un chiffre', 'instructions' => 'Vide = garder les chiffres par défaut.', 'sub_fields' => array(
		$T( 'sfld_tre_fn', 'Chiffre', '' ), $T( 'sfld_tre_fl', 'Libellé', '' ),
	) );
	$f[] = $T( 'sfld_tre_link_l', 'Lien — libellé', 'See the projects →' );
	$f[] = $T( 'sfld_tre_link_u', 'Lien — URL', 'https://tree-nation.com/' );

	/* ---- V. Note ---- */
	$f[] = $tab( 'stab_note', 'V — Note' );
	$f[] = $T( 'sfld_note_lb', 'Libellé du chapitre', 'A note on our language' );
	$f[] = $T( 'sfld_note_txt', 'Note (Playfair italique)', 'We have chosen to describe only what we actually do. Our contributions to heritage and reforestation are real and specific, and we are glad to detail them — but we make no claim that they neutralise the footprint of a journey, and we use no label we have not earned. We would rather be trusted on what we say than admired for what we imply.', 'textarea', 'Formulation validée juridiquement : ne pas y introduire de promesse de neutralité carbone.' );

	/* ---- Clôture ---- */
	$f[] = $tab( 'stab_cta', 'Clôture' );
	$f[] = $T( 'sfld_cta_title', 'Titre', 'Journeys that' );
	$f[] = $T( 'sfld_cta_title_em', 'Titre — italique doré', 'give as they go' );
	$f[] = $T( 'sfld_cta_p', 'Paragraphe', 'Tell us the pace you dream of, the places that call to you, the people you would like to meet. We will shape the journey around it.', 'textarea' );
	$f[] = $T( 'sfld_cta_btn_l', 'Bouton — libellé', 'Tailor my trip' );
	$f[] = $T( 'sfld_cta_btn_u', 'Bouton — lien', '/tailor-my-trip/' );

	acf_add_local_field_group( array(
		'key'      => 'group_aav_lb_sustainability',
		'title'    => 'AAV — Page : Sustainability',
		'fields'   => $f,
		'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/aav-sustainability' ) ) ),
	) );
} );

function aav_lb_render_sustainability( $block, $content = '', $is_preview = false ) {

	if ( $is_preview || is_admin() ) {
		aav_lb_editor_placeholder( 'AAV — Page : Sustainability' );
		return;
	}

	/* Surcharge HTML du modele : si presente, elle remplace le rendu dynamique */
	if ( aav_lb_maybe_render_override( 'sustainability' ) ) return;

	$f    = function ( $n, $d = '' ) { $v = get_field( $n ); return ( $v === '' || $v === null || $v === false ) ? $d : $v; };
	$rows = function ( $n, $fb ) { $v = get_field( $n ); return ( is_array( $v ) && count( $v ) ) ? $v : $fb; };
	$img_tag = function ( $id, $alt, $default_url = '' ) {
		$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
		if ( ! $url && $default_url ) { $did = aav_lb_url_to_id( $default_url ); $url = $did ? wp_get_attachment_image_url( $did, 'full' ) : $default_url; }
		if ( ! $url ) return '';
		return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy" onerror="this.style.display=\'none\'">';
	};

	$P  = 'https://www.aavluxurytravel.com/wp-content/uploads/2026/07/';
	$dp = array(
		'hero' => $P . 'AdobeStock_513153814-1-scaled.jpeg',
		'fon'  => $P . 'fondation-patrimoine.jpg',
		'tre'  => $P . 'Tree-Nation-Logo.jpg',
	);

	$def_fon_figs = array(
		array( 'fon_fn' => '$10,000', 'fon_fl' => 'Contributed since 2013' ),
		array( 'fon_fn' => '25',      'fon_fl' => 'Years of the Fondation' ),
	);
	$def_tre_figs = array(
		array( 'tre_fn' => '10,250', 'tre_fl' => 'Trees funded by AAV' ),
		array( 'tre_fn' => '2015',   'tre_fl' => 'Since' ),
	);
	$def_way = array(
		array( 'way_h' => 'The people who belong there', 'way_d' => 'Guides, drivers and hosts are local, independent and engaged directly — the surest way to a journey with depth, and to keeping value where it was created.' ),
		array( 'way_h' => 'Houses with a family name',   'way_d' => 'Family-owned châteaux, palazzi, quintas and independent hoteliers are our natural preference — for their character, and for the households who keep them alive.' ),
		array( 'way_h' => 'The luxury of slowness',      'way_d' => 'Longer stays in fewer places, rather than a new city every other morning. It is how a country is truly felt — and it asks far less of the road.' ),
		array( 'way_h' => 'Beyond the crowds',           'way_d' => 'We favour the regions and seasons where beauty is not shared with a queue — a quiet corner of Andalusia, the Douro out of season. The experience is incomparably finer, and the celebrated sites are given room to breathe.' ),
		array( 'way_h' => 'The pleasure of the train',   'way_d' => 'Across France, Italy, Spain, Switzerland and beyond, the train is frequently the finer way to travel — city centre to city centre, landscape unfolding at the window. We suggest it whenever it serves you best.' ),
		array( 'way_h' => 'Savoir-faire, sustained',     'way_d' => 'Ateliers, growers and master craftspeople are woven into your days. These encounters are among the most memorable — and they sustain trades that would otherwise fade.' ),
	);

	$fon_figs = $rows( 'fon_figs', $def_fon_figs );
	$tre_figs = $rows( 'tre_figs', $def_tre_figs );
	$way      = $rows( 'way_items', $def_way );

	$video = trim( (string) $f( 'hero_video', $P . 'Sustainability-AAV-V1.mp4' ) );
	$hero_photo_id  = (int) get_field( 'hero_photo' );
	$hero_photo_url = $hero_photo_id ? wp_get_attachment_image_url( $hero_photo_id, 'full' ) : '';
	if ( ! $hero_photo_url ) {
		$hid = aav_lb_url_to_id( $dp['hero'] );
		$hero_photo_url = $hid ? wp_get_attachment_image_url( $hid, 'full' ) : $dp['hero'];
	}

	$css = aav_lb_css( 'sustainability' );
	ob_start();
	if ( $css ) echo '<style>' . $css . '</style>';
	?>
<div class="asu-root">

  <!-- 1. HÉRO -->
  <section class="asu-hero">
    <div class="asu-hero-media" aria-hidden="true">
      <?php if ( $hero_photo_url ) : ?>
      <img class="asu-hero-still" src="<?php echo esc_url( $hero_photo_url ); ?>" alt="" loading="eager" onerror="this.style.display='none'">
      <?php endif; ?>
      <?php if ( $video ) : ?>
      <video class="asu-hero-vid" autoplay muted loop playsinline preload="metadata"<?php echo $hero_photo_url ? ' poster="' . esc_url( $hero_photo_url ) . '"' : ''; ?>>
        <source src="<?php echo esc_url( $video ); ?>" type="video/mp4">
      </video>
      <?php endif; ?>
    </div>
    <div class="asu-hero-scrim" aria-hidden="true"></div>
    <h1 class="asu-rv"><?php echo esc_html( $f( 'hero_title' ) ); ?> <em><?php echo esc_html( $f( 'hero_title_em' ) ); ?></em></h1>
    <div class="asu-hero-rule asu-rv asu-d1" aria-hidden="true"></div>
    <p class="asu-rv asu-d2"><?php echo esc_html( $f( 'hero_sub' ) ); ?></p>
    <div class="asu-hero-edge" aria-hidden="true"></div>
  </section>

  <!-- 2. ENGAGEMENT -->
  <section class="asu-honest">
    <div class="asu-wrap">
      <div class="asu-chap asu-rv"><span class="rn">I</span><span class="lb"><?php echo esc_html( $f( 'com_lb' ) ); ?></span></div>
      <p class="asu-statement asu-rv asu-d1"><?php echo esc_html( $f( 'com_st' ) ); ?> <span class="asu-em"><?php echo esc_html( $f( 'com_st_em' ) ); ?></span></p>
      <div class="cols">
        <div class="asu-rv asu-d2">
          <p><?php echo esc_html( $f( 'com_p1' ) ); ?></p>
          <p><?php echo esc_html( $f( 'com_p2' ) ); ?></p>
        </div>
        <div class="asu-rv asu-d3">
          <p><?php echo esc_html( $f( 'com_p3' ) ); ?></p>
          <p><?php echo esc_html( $f( 'com_p4' ) ); ?></p>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. FONDATION DU PATRIMOINE -->
  <section class="asu-focus asu-ivory">
    <div class="asu-wrap">
      <div class="grid">
        <div class="visual asu-visual asu-rv">
          <?php echo $img_tag( (int) get_field( 'fon_photo' ), 'Fondation du Patrimoine', $dp['fon'] ); ?>
        </div>
        <div class="copy asu-rv asu-d2">
          <div class="asu-chap"><span class="rn">II</span><span class="lb"><?php echo esc_html( $f( 'fon_lb' ) ); ?></span></div>
          <h2><?php echo esc_html( $f( 'fon_title' ) ); ?> <em><?php echo esc_html( $f( 'fon_title_em' ) ); ?></em></h2>
          <p class="lead"><?php echo esc_html( $f( 'fon_lead' ) ); ?></p>
          <p><?php echo esc_html( $f( 'fon_p1' ) ); ?></p>
          <p><?php echo esc_html( $f( 'fon_p2' ) ); ?></p>
          <?php if ( $fon_figs ) : ?>
          <div class="figures">
            <?php foreach ( $fon_figs as $r ) : ?>
            <div><div class="n"><?php echo esc_html( isset( $r['fon_fn'] ) ? $r['fon_fn'] : '' ); ?></div><div class="l"><?php echo esc_html( isset( $r['fon_fl'] ) ? $r['fon_fl'] : '' ); ?></div></div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if ( $f( 'fon_link_l' ) ) : ?>
          <a class="link" href="<?php echo esc_url( $f( 'fon_link_u' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $f( 'fon_link_l' ) ); ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. NOS CHOIX -->
  <section class="asu-local asu-dark">
    <div class="asu-wrap">
      <div class="head asu-rv">
        <div class="asu-chap"><span class="rn">III</span><span class="lb"><?php echo esc_html( $f( 'way_lb' ) ); ?></span></div>
        <h2><?php echo esc_html( $f( 'way_title' ) ); ?> <em><?php echo esc_html( $f( 'way_title_em' ) ); ?></em></h2>
        <p class="asu-lede"><?php echo esc_html( $f( 'way_intro' ) ); ?></p>
      </div>
      <div class="list">
        <?php $i = 0; foreach ( $way as $r ) : $i++; $d = ( $i - 1 ) % 3; ?>
        <div class="item asu-rv<?php echo $d ? ' asu-d' . $d : ''; ?>">
          <h3><?php echo esc_html( isset( $r['way_h'] ) ? $r['way_h'] : '' ); ?></h3>
          <p><?php echo esc_html( isset( $r['way_d'] ) ? $r['way_d'] : '' ); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- 5. TREE-NATION -->
  <section class="asu-focus flip">
    <div class="asu-wrap">
      <div class="grid">
        <div class="visual asu-visual asu-rv">
          <?php echo $img_tag( (int) get_field( 'tre_photo' ), 'Tree-Nation', $dp['tre'] ); ?>
        </div>
        <div class="copy asu-rv asu-d2">
          <div class="asu-chap"><span class="rn">IV</span><span class="lb"><?php echo esc_html( $f( 'tre_lb' ) ); ?></span></div>
          <h2><?php echo esc_html( $f( 'tre_title' ) ); ?> <em><?php echo esc_html( $f( 'tre_title_em' ) ); ?></em></h2>
          <p class="lead"><?php echo esc_html( $f( 'tre_lead' ) ); ?></p>
          <p><?php echo esc_html( $f( 'tre_p1' ) ); ?></p>
          <p><?php echo esc_html( $f( 'tre_p2' ) ); ?></p>
          <?php if ( $tre_figs ) : ?>
          <div class="figures">
            <?php foreach ( $tre_figs as $r ) : ?>
            <div><div class="n"><?php echo esc_html( isset( $r['tre_fn'] ) ? $r['tre_fn'] : '' ); ?></div><div class="l"><?php echo esc_html( isset( $r['tre_fl'] ) ? $r['tre_fl'] : '' ); ?></div></div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if ( $f( 'tre_link_l' ) ) : ?>
          <a class="link" href="<?php echo esc_url( $f( 'tre_link_u' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $f( 'tre_link_l' ) ); ?></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- 6. NOTE -->
  <section class="asu-limits asu-ivory">
    <div class="asu-narrow">
      <div class="box asu-rv">
        <div class="asu-chap"><span class="rn">V</span><span class="lb"><?php echo esc_html( $f( 'note_lb' ) ); ?></span></div>
        <p class="asu-note"><?php echo esc_html( $f( 'note_txt' ) ); ?></p>
      </div>
    </div>
  </section>

  <!-- 7. CLÔTURE -->
  <section class="asu-cta asu-dark">
    <h2 class="asu-rv"><?php echo esc_html( $f( 'cta_title' ) ); ?> <em><?php echo esc_html( $f( 'cta_title_em' ) ); ?></em></h2>
    <div class="rule asu-rv asu-d1" aria-hidden="true"></div>
    <p class="asu-rv asu-d2"><?php echo esc_html( $f( 'cta_p' ) ); ?></p>
    <?php if ( $f( 'cta_btn_l' ) ) : ?>
    <a class="asu-btn asu-rv asu-d3" href="<?php echo esc_url( $f( 'cta_btn_u' ) ); ?>"><?php echo esc_html( $f( 'cta_btn_l' ) ); ?></a>
    <?php endif; ?>
  </section>

</div>
<script>
(function(){
  "use strict";
  var root = document.querySelector('.asu-root');
  if (!root || root.dataset.asuInit) return;
  root.dataset.asuInit = '1';
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var inEditor = document.body && document.body.classList.contains('block-editor-page');
  if ('IntersectionObserver' in window && !reduce && !inEditor){
    root.classList.add('asu-anim');
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (e.isIntersecting){ e.target.classList.add('asu-in'); io.unobserve(e.target); }
      });
    },{threshold:.12,rootMargin:'0px 0px -40px 0px'});
    root.querySelectorAll('.asu-rv').forEach(function(el){ io.observe(el); });
  }
  var vid = root.querySelector('.asu-hero-media video');
  if (vid && reduce){ vid.removeAttribute('autoplay'); vid.pause(); }
})();
</script>
	<?php
	echo ob_get_clean();
}

/* ================================================================== *
 * BLOC LITTLE BLACK BOOK — page « The Little Black Book »
 * -----------------------------------------------------------------
 * Conventions identiques aux blocs Sustainability / Our Story :
 *   - clés de champs : bfld_… / accordéons : btab_…  (name = substr($key,5))
 *   - répéteurs sans valeur par défaut → repli sur tableaux $def_* au rendu
 *   - photos : ID de médiathèque, repli URL via aav_lb_url_to_id()
 *     (les URL par défaut pointent vers des visuels déjà présents dans
 *     la médiathèque du site ; une URL de vignette -400x300 est ramenée
 *     au fichier d'origine avant résolution)
 *   - CSS : assets/little-black-book.css (préfixe .alb-)
 * ================================================================== */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) return;
	acf_register_block_type( array(
		'name'              => 'aav-little-black-book',
		'title'             => __( 'AAV — Page : The Little Black Book', 'aav-lb' ),
		'description'       => __( 'Éditable : textes, photos, pays, pages du carnet et liens. Structure figée.', 'aav-lb' ),
		'category'          => 'formatting',
		'icon'              => 'book-alt',
		'keywords'          => array( 'aav', 'little black book', 'carnet', 'network', 'access' ),
		'mode'              => 'edit',
		'api_version'       => 3,
		'acf_block_version' => 3,
		'render_preview'    => false,
		'supports'          => array( 'align' => false, 'multiple' => false, 'jsx' => true, 'mode' => false ),
		'render_callback'   => 'aav_lb_render_little_black_book',
	) );
} );

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$T = function ( $key, $label, $default = '', $type = 'text', $instr = '' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => $type, 'default_value' => $default, 'instructions' => $instr );
	};
	$tab = function ( $key, $label ) { return array( 'key' => $key, 'label' => $label, 'type' => 'accordion', 'open' => 0, 'multi_expand' => 1 ); };
	$img = function ( $key, $label, $instr = 'Vide = la photo par défaut (déjà dans la médiathèque) reste affichée.' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all', 'instructions' => $instr );
	};
	$sub_img = function ( $key, $label ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail', 'library' => 'all' );
	};

	$f = array();

	/* ---- Héro (la couverture) ---- */
	$f[] = $tab( 'btab_hero', 'Héro (couverture)' );
	$f[] = $T( 'bfld_hero_video', 'Vidéo de fond (URL .mp4)', '', 'text', 'Optionnel. MP4 H.264, 1080p, boucle 10 à 20 s, sans son, idéalement moins de 10 Mo. Vide = photo seule, avec un lent zoom.' );
	$f[] = $img( 'bfld_hero_photo', 'Image de fond', 'Affichée pendant le chargement de la vidéo et en secours. Vide = le carnet ancien de la page actuelle.' );
	$f[] = $T( 'bfld_hero_kicker', 'Surtitre', '20 Years · One Exceptional Network' );
	$f[] = $T( 'bfld_hero_title', 'Titre', 'The Little' );
	$f[] = $T( 'bfld_hero_title_em', 'Titre : italique doré', 'Black Book' );
	$f[] = $T( 'bfld_hero_sub', 'Accroche', 'Twenty years of relationships have opened doors to exceptional people, places and experiences. Our Little Black Book is where local expertise, trusted connections and the unexpected come together.', 'textarea' );
	$f[] = $T( 'bfld_hero_meta', 'Ligne discrète sous l\'accroche', 'Twenty years · Six countries · One address book', 'text', 'Vide = masquée.' );
	$f[] = $T( 'bfld_hero_cue', 'Invitation à défiler', 'Open the book', 'text', 'Vide = masquée.' );
	$f[] = $T( 'bfld_toc_lb', 'Sommaire : libellé', 'Contents', 'text', 'Barre de sommaire sous le héro (liens vers les chapitres). Vide = barre masquée.' );

	/* ---- I. Vingt ans ---- */
	$f[] = $tab( 'btab_mak', 'I : Twenty years in the making' );
	$f[] = $T( 'bfld_mak_lb', 'Libellé du chapitre', 'Twenty years in the making' );
	$f[] = $T( 'bfld_mak_st', 'Grande phrase', 'The most memorable journeys begin with', 'textarea' );
	$f[] = $T( 'bfld_mak_st_em', 'Grande phrase : partie italique', 'who you know.' );
	$f[] = $T( 'bfld_mak_p1', 'Paragraphe 1', 'For more than twenty years, AAV has built trusted relationships with the people who make Europe extraordinary: château owners and vignerons, chefs and artisans, curators, guides and private hosts. These are not suppliers found in a directory. They are friends of the house, met in person and returned to year after year.', 'textarea' );
	$f[] = $T( 'bfld_mak_p2', 'Paragraphe 2', 'This is not simply a network of partners. It is a Little Black Book built over two decades, one relationship at a time: the numbers that answer, the doors that open, the names that vouch for ours. It is, quite simply, the reason our journeys feel the way they do.', 'textarea' );
	$f[] = $img( 'bfld_mak_photo1', 'Photo principale (portrait)' );
	$f[] = $img( 'bfld_mak_photo2', 'Photo en médaillon (se superpose)' );
	$f[] = $T( 'bfld_mak_cap', 'Légende sous les photos', 'Met in person. Kept for years.', 'text', 'Vide = masquée.' );
	$f[] = $T( 'bfld_mak_mark', 'Filigrane (grand chiffre en fond)', '20', 'text', 'Vide = masqué.' );
	$f[] = array( 'key' => 'bfld_mak_figs', 'label' => 'Chiffres', 'name' => 'mak_figs', 'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Ajouter un chiffre', 'instructions' => 'Vide = garder les chiffres par défaut. Pour n\'en afficher aucun, saisir une ligne vide.', 'sub_fields' => array(
		$T( 'bfld_mak_fn', 'Chiffre', '' ), $T( 'bfld_mak_fl', 'Libellé', '' ),
	) );
	$f[] = $T( 'bfld_mak_people_lb', 'Galerie : surtitre', 'The people we know', 'text', 'Vide = galerie masquée.' );
	$f[] = array( 'key' => 'bfld_mak_people', 'label' => 'Galerie : les personnes', 'name' => 'mak_people', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter une personne', 'instructions' => 'Vide = garder les 5 portraits par défaut. Photos au format portrait de préférence.', 'sub_fields' => array(
		$sub_img( 'bfld_ppl_ph', 'Photo' ), $T( 'bfld_ppl_role', 'Rôle (Playfair italique)', '' ), $T( 'bfld_ppl_line', 'Une ligne', '', 'textarea' ),
	) );

	/* ---- II. Europe ---- */
	$f[] = $tab( 'btab_eur', 'II : Our European expertise' );
	$f[] = $T( 'bfld_eur_lb', 'Libellé du chapitre', 'Our European expertise' );
	$f[] = $T( 'bfld_eur_title', 'Titre', 'Twenty years of connections' );
	$f[] = $T( 'bfld_eur_title_em', 'Titre : italique doré', 'across Europe' );
	$f[] = $T( 'bfld_eur_intro', 'Introduction', 'France is the heart of what we do, and where most of our journeys unfold. Yet the same relationships, cultivated with the same patience, reach across Italy, Switzerland, the United Kingdom, Spain and Portugal. In every country, our specialists live where you travel, and the people they trust become the people you meet.', 'textarea' );
	$f[] = $T( 'bfld_eur_ribbon', 'Ruban défilant (lieux, séparés par des virgules)', 'Paris, Bordeaux, Burgundy, Champagne, the Loire, Provence, the Riviera, Alsace, Piedmont, Tuscany, Lake Geneva, Zermatt, London, the Cotswolds, Andalusia, the Basque Country, the Douro, Lisbon', 'textarea', 'Vide = ruban masqué.' );
	$f[] = array( 'key' => 'bfld_eur_items', 'label' => 'Pays (index du carnet)', 'name' => 'eur_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un pays', 'instructions' => 'Vide = garder les 6 pays par défaut. Sans photo, une planche typographique (nom du pays + code) est affichée.', 'sub_fields' => array(
		$T( 'bfld_eur_n', 'Pays', '' ), $T( 'bfld_eur_c', 'Code (2 lettres)', '' ), $T( 'bfld_eur_d', 'Une ligne', '', 'textarea' ),
		$sub_img( 'bfld_eur_ph', 'Photo' ),
	) );
	$f[] = $T( 'bfld_eur_foot', 'Phrase de fin (italique)', 'And, when your story calls for it, beyond.', 'text', 'Vide = masquée.' );

	/* ---- III. Behind closed doors ---- */
	$f[] = $tab( 'btab_dor', 'III : Behind closed doors' );
	$f[] = $img( 'bfld_dor_photo', 'Photo de fond (pleine largeur)' );
	$f[] = $T( 'bfld_dor_seal', 'Monogramme du sceau', 'AAV', 'text', 'Vide = sceau masqué.' );
	$f[] = $T( 'bfld_dor_lb', 'Libellé du chapitre', 'The Little Black Book' );
	$f[] = $T( 'bfld_dor_title', 'Titre', 'Behind' );
	$f[] = $T( 'bfld_dor_title_em', 'Titre : italique doré', 'closed doors' );
	$f[] = $T( 'bfld_dor_p1', 'Chapeau', 'Some of the most memorable experiences cannot simply be booked. They are offered, by people who choose whom they welcome. They exist outside opening hours and off the pages of any guidebook, and they are extended only through introduction.', 'textarea' );
	$f[] = $T( 'bfld_dor_p2', 'Paragraphe', 'A private collection viewed with its owner. An exceptional estate that is still a family home. An artist\'s studio on a working morning. Each door is opened for you personally, always with discretion, and never simply for the sake of exclusivity.', 'textarea' );
	$f[] = $T( 'bfld_dor_pages_lb', 'Pages du carnet : surtitre', 'Pages from the book' );
	$f[] = $T( 'bfld_dor_pages_t', 'Pages du carnet : titre', 'A few doors' );
	$f[] = $T( 'bfld_dor_pages_te', 'Pages du carnet : titre italique', 'we have opened' );
	$f[] = array( 'key' => 'bfld_dor_pages', 'label' => 'Pages du carnet', 'name' => 'dor_pages', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter une page', 'instructions' => 'Vide = garder les 6 exemples par défaut (avec leurs photos).', 'sub_fields' => array(
		$sub_img( 'bfld_dor_pp', 'Photo' ), $T( 'bfld_dor_ph', 'Titre', '' ), $T( 'bfld_dor_pd', 'Description', '', 'textarea' ),
	) );

	/* ---- IV. Comment nous ouvrons les portes ---- */
	$f[] = $tab( 'btab_way', 'IV : How we open doors' );
	$f[] = $T( 'bfld_way_lb', 'Libellé du chapitre', 'How we open doors' );
	$f[] = $T( 'bfld_way_title', 'Titre', 'Relationships,' );
	$f[] = $T( 'bfld_way_title_em', 'Titre : italique doré', 'not reservations' );
	$f[] = $T( 'bfld_way_intro', 'Introduction', 'What sets these experiences apart is not that they are rare. It is that they are personal. Three principles guide every page of our Little Black Book, and you will recognise them in any journey we write for you.', 'textarea' );
	$f[] = array( 'key' => 'bfld_way_items', 'label' => 'Principes', 'name' => 'way_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un principe', 'instructions' => 'Vide = garder les 3 principes par défaut.', 'sub_fields' => array(
		$T( 'bfld_way_h', 'Titre', '' ), $T( 'bfld_way_d', 'Description', '', 'textarea' ),
	) );

	/* ---- Citation pleine largeur ---- */
	$f[] = $tab( 'btab_quo', 'Citation pleine largeur' );
	$f[] = $img( 'bfld_quo_photo', 'Photo de fond' );
	$f[] = $T( 'bfld_quo_txt', 'Citation (Playfair italique)', 'Doors do not open for reservations. They open for friends.', 'textarea', 'Vide = bande masquée.' );
	$f[] = $T( 'bfld_quo_by', 'Signature', 'The AAV way, for twenty years', 'text', 'Vide = masquée.' );

	/* ---- V. Note ---- */
	$f[] = $tab( 'btab_note', 'V : A word on discretion' );
	$f[] = $T( 'bfld_note_lb', 'Libellé du chapitre', 'A word on discretion' );
	$f[] = $T( 'bfld_note_txt', 'Note (Playfair italique)', 'Many of the doors in our Little Black Book open only because we have promised they will stay closed to everyone else. We keep that promise. It is why some of what we do cannot appear on this page, and why our guests are welcomed as friends rather than visitors.', 'textarea', 'Vide = section masquée.' );

	/* ---- Clôture ---- */
	$f[] = $tab( 'btab_cta', 'Clôture' );
	$f[] = $img( 'bfld_cta_photo', 'Photo de fond' );
	$f[] = $T( 'bfld_cta_lb', 'Surtitre', 'Your journey starts here' );
	$f[] = $T( 'bfld_cta_title', 'Titre', 'Open the' );
	$f[] = $T( 'bfld_cta_title_em', 'Titre : italique doré', 'Little Black Book' );
	$f[] = $T( 'bfld_cta_p', 'Paragraphe', 'Tell us what you are passionate about: the artists, the craft, the wine, the history, the table. We will open the pages that speak to it and shape a journey around encounters you will not find anywhere else, including some you may never have known were possible.', 'textarea' );
	$f[] = $T( 'bfld_cta_btn_l', 'Bouton : libellé', 'Tailor my trip' );
	$f[] = $T( 'bfld_cta_btn_u', 'Bouton : lien', '/tailor-my-trip/', 'text', 'URL complète, chemin (/tailor-my-trip/) ou ancre (#plan) résolue sur la page.' );
	$f[] = $T( 'bfld_cta_link_l', 'Lien secondaire : libellé', 'Or read our story', 'text', 'Vide = masqué.' );
	$f[] = $T( 'bfld_cta_link_u', 'Lien secondaire : URL', '', 'text', 'Vide = la page « Our Story » du site si elle existe.' );

	acf_add_local_field_group( array(
		'key'      => 'group_aav_lb_little_black_book',
		'title'    => 'AAV — Page : The Little Black Book',
		'fields'   => $f,
		'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/aav-little-black-book' ) ) ),
	) );
} );

function aav_lb_render_little_black_book( $block, $content = '', $is_preview = false ) {

	if ( $is_preview || is_admin() ) {
		aav_lb_editor_placeholder( 'AAV — Page : The Little Black Book' );
		return;
	}

	/* Surcharge HTML du modele : si presente, elle remplace le rendu dynamique */
	if ( aav_lb_maybe_render_override( 'little-black-book' ) ) return;

	/* Valeur du champ ; si le bloc n'a jamais été enregistré depuis l'éditeur,
	   repli sur la valeur par défaut déclarée (clé bfld_…) pour que la page
	   s'affiche complète dès l'insertion du modèle. */
	$f    = function ( $n, $d = '' ) {
		$v = get_field( $n );
		if ( $v !== '' && $v !== null && $v !== false ) return $v;
		if ( '' === $d && function_exists( 'acf_get_field' ) ) {
			$fo = acf_get_field( 'bfld_' . $n );
			if ( is_array( $fo ) && isset( $fo['default_value'] ) && '' !== $fo['default_value'] ) return $fo['default_value'];
		}
		return $d;
	};
	$rows = function ( $n, $fb ) { $v = get_field( $n ); return ( is_array( $v ) && count( $v ) ) ? $v : $fb; };

	/* URL d'image : ID de médiathèque en priorité, sinon URL par défaut
	   ramenée à son fichier d'origine (…-400x300.jpg → ….jpg / …-scaled.jpg)
	   et résolue en ID quand c'est possible, pour servir la bonne taille. */
	$img_url = function ( $id, $default_url = '', $size = 'full' ) {
		$url = $id ? wp_get_attachment_image_url( (int) $id, $size ) : '';
		if ( $url ) return $url;
		if ( ! $default_url ) return '';
		$base = preg_replace( '/-\d+x\d+(\.[a-z]{3,4})$/i', '$1', $default_url );
		$try  = array_unique( array( $default_url, $base, preg_replace( '/(\.[a-z]{3,4})$/i', '-scaled$1', $base ) ) );
		foreach ( $try as $u ) {
			$did = aav_lb_url_to_id( $u );
			if ( $did ) { $r = wp_get_attachment_image_url( $did, $size ); if ( $r ) return $r; }
		}
		return $base;
	};
	$roman = function ( $n ) {
		$map = array( 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1 ); $r = '';
		foreach ( $map as $k => $v ) { while ( $n >= $v ) { $r .= $k; $n -= $v; } }
		return $r;
	};

	/* Visuels par défaut : tous déjà présents dans la médiathèque du site */
	$U  = 'https://www.aavluxurytravel.com/wp-content/uploads/';
	$dp = array(
		'hero'   => $U . '2021/03/shutterstock_125765180-scaled.jpg',
		'mak1'   => $U . '2026/07/Eric_Strasbourg.jpg',
		'mak2'   => $U . '2026/07/AdobeStock_535355681-scaled.jpeg',
		'dor'    => $U . '2021/02/shutterstock_156171842-scaled.jpg',
		'quo'    => $U . '2021/01/Paris-Pont-Alexandre32.jpg',
		'cta'    => $U . '2026/09/honeymooninfrance-1-scaled.jpeg',
	);

	$def_figs = array(
		array( 'mak_fn' => '20', 'mak_fl' => 'Years of relationships' ),
		array( 'mak_fn' => '6',  'mak_fl' => 'Countries, one house' ),
		array( 'mak_fn' => '1',  'mak_fl' => 'Little Black Book' ),
	);
	$def_people = array(
		array( '_url' => $U . '2026/07/AdobeStock_306421927-1-scaled.jpeg', 'ppl_role' => 'The vigneron',  'ppl_line' => 'Who pours the vintage that never leaves the estate.' ),
		array( '_url' => $U . '2026/09/Gourmet6-scaled.jpg',                 'ppl_role' => 'The chef',      'ppl_line' => 'Who cooks for you at a table that has no menu.' ),
		array( '_url' => $U . '2021/01/shutterstock480655321.jpg',           'ppl_role' => 'The jeweller',  'ppl_line' => 'Who opens the archive, not the boutique.' ),
		array( '_url' => $U . '2021/01/chateau-medieval2.jpg',               'ppl_role' => 'The owner',     'ppl_line' => 'Whose family has kept the château for generations.' ),
		array( '_url' => $U . '2026/09/Private-Driver-1-scaled.jpg',         'ppl_role' => 'The driver',    'ppl_line' => 'Who knows the road, the season and your name.' ),
	);
	$def_eur = array(
		array( 'eur_n' => 'France',         'eur_c' => 'FR', '_url' => $U . '2026/09/Chenonceau-hot-air-balloon-small.jpg', 'eur_d' => 'The heart of the house. Châteaux, ateliers and vineyards we have known, and been welcomed in, for two decades.' ),
		array( 'eur_n' => 'Italy',          'eur_c' => 'IT', '_url' => $U . '2026/07/AdobeStock_191204993-scaled.jpeg',      'eur_d' => 'From Piedmont to Puglia: the families, cellars and workshops behind the façades, and the tables that never needed a sign.' ),
		array( 'eur_n' => 'Switzerland',    'eur_c' => 'CH', '_url' => $U . '2026/07/AdobeStock_353324432-scaled.jpeg',      'eur_d' => 'Alpine addresses, lakeside houses and the quiet precision of the people who keep them.' ),
		array( 'eur_n' => 'United Kingdom', 'eur_c' => 'UK', '_url' => $U . '2021/02/shutterstock_1257872494.jpg',           'eur_d' => 'Country estates, private rooms in London and the people who hold the keys.' ),
		array( 'eur_n' => 'Spain',          'eur_c' => 'ES', '_url' => '',                                                    'eur_d' => 'Andalusian cortijos, Basque kitchens and artisans no guidebook will ever list.' ),
		array( 'eur_n' => 'Portugal',       'eur_c' => 'PT', '_url' => '',                                                    'eur_d' => 'Quintas of the Douro, Lisbon ateliers and a coastline known by first name.' ),
	);
	$def_pages = array(
		array( '_url' => $U . '2021/02/shutterstock_217564789.jpg',   'dor_ph' => 'A private collection',                'dor_pd' => 'Viewed after hours with its owner, in rooms that never open to the public.' ),
		array( '_url' => $U . '2021/01/chateau-medieval2.jpg',        'dor_ph' => 'An estate that is still a home',      'dor_pd' => 'Lunch served by the family who have kept the château for generations, at their own table.' ),
		array( '_url' => $U . '2026/09/Gourmet6-scaled.jpg',          'dor_ph' => 'A table that is not a restaurant',    'dor_pd' => 'A chef cooking for you alone, in a kitchen that has never printed a menu.' ),
		array( '_url' => $U . '2021/02/Shopping13.jpg',               'dor_ph' => 'Coco Chanel\'s private apartments',   'dor_pd' => 'Rue Cambon, by introduction, and a story told by those who know it best.' ),
		array( '_url' => $U . '2021/01/shutterstock480655321.jpg',    'dor_ph' => 'A jeweller\'s archive on Place Vendôme', 'dor_pd' => 'Historic collections shown by the house itself, behind a door without a handle.' ),
		array( '_url' => $U . '2026/09/Burgundy4-small.jpg',          'dor_ph' => 'A cellar without a sign',             'dor_pd' => 'A vigneron\'s own reserve in Bordeaux, Burgundy or Champagne, opened for a table of four.' ),
	);
	$def_way = array(
		array( 'way_h' => 'Earned in person',      'way_d' => 'Every introduction in our book was made face to face and kept over years. It is why doors open for our guests that do not open for a booking platform.' ),
		array( 'way_h' => 'Discretion, always',    'way_d' => 'Many of our hosts welcome guests precisely because we never advertise them. What happens behind a closed door stays there.' ),
		array( 'way_h' => 'Never for its own sake', 'way_d' => 'Access matters only when it means something to you. We open a door because it belongs in your story, not because it is hard to open.' ),
	);

	$figs   = $rows( 'mak_figs', $def_figs );
	$people = $rows( 'mak_people', $def_people );
	$eur    = $rows( 'eur_items', $def_eur );
	$pages  = $rows( 'dor_pages', $def_pages );
	$way    = $rows( 'way_items', $def_way );

	$video    = trim( (string) $f( 'hero_video', '' ) );
	$hero_url = $img_url( get_field( 'hero_photo' ), $dp['hero'] );
	$mak1_url = $img_url( get_field( 'mak_photo1' ), $dp['mak1'], 'large' );
	$mak2_url = $img_url( get_field( 'mak_photo2' ), $dp['mak2'], 'large' );
	$dor_url  = $img_url( get_field( 'dor_photo' ), $dp['dor'] );
	$quo_url  = $img_url( get_field( 'quo_photo' ), $dp['quo'] );
	$cta_url  = $img_url( get_field( 'cta_photo' ), $dp['cta'] );

	$btn_url = aav_lb_cta_url( $f( 'cta_btn_u' ), '/tailor-my-trip/' );
	$alt_url = trim( (string) $f( 'cta_link_u', '' ) );
	if ( '' === $alt_url ) {
		$story = get_page_by_path( 'our-story' );
		if ( $story && 'publish' === $story->post_status ) $alt_url = get_permalink( $story );
	} else {
		$alt_url = aav_lb_cta_url( $alt_url, '' );
	}

	$ribbon = array_filter( array_map( 'trim', explode( ',', (string) $f( 'eur_ribbon', '' ) ) ) );
	$toc = array(
		array( 'I',   '#alb-making', $f( 'mak_lb' ) ),
		array( 'II',  '#alb-europe', $f( 'eur_lb' ) ),
		array( 'III', '#alb-doors',  $f( 'dor_lb' ) ),
		array( 'IV',  '#alb-way',    $f( 'way_lb' ) ),
		array( 'V',   '#alb-note',   $f( 'note_lb' ) ),
	);

	$css = aav_lb_css( 'little-black-book' );
	ob_start();
	if ( $css ) echo '<style>' . $css . '</style>';
	?>
<div class="alb-root">

  <!-- 1. HÉRO : la couverture -->
  <section class="alb-hero">
    <div class="alb-hero-media" aria-hidden="true">
      <?php if ( $hero_url ) : ?>
      <img class="alb-hero-still<?php echo $video ? '' : ' alb-kb'; ?>" src="<?php echo esc_url( $hero_url ); ?>" alt="" loading="eager" onerror="this.style.display='none'">
      <?php endif; ?>
      <?php if ( $video ) : ?>
      <video class="alb-hero-vid" autoplay muted loop playsinline preload="metadata"<?php echo $hero_url ? ' poster="' . esc_url( $hero_url ) . '"' : ''; ?>>
        <source src="<?php echo esc_url( $video ); ?>" type="video/mp4">
      </video>
      <?php endif; ?>
    </div>
    <div class="alb-hero-scrim" aria-hidden="true"></div>
    <div class="alb-cover" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
    <?php if ( $f( 'hero_kicker' ) ) : ?>
    <span class="alb-kicker alb-rv"><?php echo esc_html( $f( 'hero_kicker' ) ); ?></span>
    <?php endif; ?>
    <h1 class="alb-rv alb-d1"><?php echo esc_html( $f( 'hero_title' ) ); ?> <em><?php echo esc_html( $f( 'hero_title_em' ) ); ?></em></h1>
    <div class="alb-hero-rule alb-rv alb-d2" aria-hidden="true"></div>
    <p class="alb-rv alb-d3"><?php echo esc_html( $f( 'hero_sub' ) ); ?></p>
    <?php if ( $f( 'hero_meta' ) ) : ?>
    <div class="alb-hero-meta alb-rv alb-d4"><?php echo esc_html( $f( 'hero_meta' ) ); ?></div>
    <?php endif; ?>
    <?php if ( $f( 'hero_cue' ) ) : ?>
    <a class="alb-hero-cue" href="<?php echo esc_url( get_permalink() . '#alb-making' ); ?>"><span><?php echo esc_html( $f( 'hero_cue' ) ); ?></span><span class="l" aria-hidden="true"></span></a>
    <?php endif; ?>
    <div class="alb-hero-edge" aria-hidden="true"></div>
  </section>

  <!-- SOMMAIRE -->
  <?php if ( $f( 'toc_lb' ) ) : ?>
  <nav class="alb-toc alb-ivory" aria-label="<?php echo esc_attr( $f( 'toc_lb' ) ); ?>">
    <div class="alb-wrap">
      <span class="lb"><?php echo esc_html( $f( 'toc_lb' ) ); ?></span>
      <ul>
        <?php foreach ( $toc as $t ) : if ( ! $t[2] ) continue; ?>
        <li><a href="<?php echo esc_url( get_permalink() . $t[1] ); ?>"><span class="rn"><?php echo esc_html( $t[0] ); ?></span><?php echo esc_html( $t[2] ); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>
  <?php endif; ?>

  <!-- 2. VINGT ANS -->
  <section class="alb-making" id="alb-making">
    <?php if ( $f( 'mak_mark' ) ) : ?>
    <div class="alb-mark" aria-hidden="true"><?php echo esc_html( $f( 'mak_mark' ) ); ?></div>
    <?php endif; ?>
    <div class="alb-folio" aria-hidden="true">i</div>
    <div class="alb-wrap">
      <div class="grid">
        <div class="copy">
          <div class="alb-chap alb-rv"><span class="rn">I</span><span class="lb"><?php echo esc_html( $f( 'mak_lb' ) ); ?></span></div>
          <p class="alb-statement alb-rv alb-d1"><?php echo esc_html( $f( 'mak_st' ) ); ?> <span class="alb-em"><?php echo esc_html( $f( 'mak_st_em' ) ); ?></span></p>
          <div class="alb-rv alb-d2"><p><?php echo esc_html( $f( 'mak_p1' ) ); ?></p></div>
          <div class="alb-rv alb-d3"><p><?php echo esc_html( $f( 'mak_p2' ) ); ?></p></div>
          <?php $figs = array_filter( $figs, function ( $r ) { return ! empty( $r['mak_fn'] ) || ! empty( $r['mak_fl'] ); } ); if ( $figs ) : ?>
          <div class="figures alb-rv alb-d3">
            <?php foreach ( $figs as $r ) : ?>
            <div><div class="n"><?php echo esc_html( isset( $r['mak_fn'] ) ? $r['mak_fn'] : '' ); ?></div><div class="l"><?php echo esc_html( isset( $r['mak_fl'] ) ? $r['mak_fl'] : '' ); ?></div></div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="alb-collage alb-rv alb-d2">
          <div class="main"><?php if ( $mak1_url ) : ?><img src="<?php echo esc_url( $mak1_url ); ?>" alt="" loading="lazy" onerror="this.style.display='none'"><?php endif; ?></div>
          <?php if ( $mak2_url ) : ?>
          <div class="inset"><img src="<?php echo esc_url( $mak2_url ); ?>" alt="" loading="lazy" onerror="this.parentNode.style.display='none'"></div>
          <?php endif; ?>
          <?php if ( $f( 'mak_cap' ) ) : ?>
          <div class="cap"><?php echo esc_html( $f( 'mak_cap' ) ); ?></div>
          <?php endif; ?>
        </div>
      </div>

      <?php if ( $f( 'mak_people_lb' ) && $people ) : ?>
      <div class="alb-people">
        <span class="alb-kicker left alb-rv"><?php echo esc_html( $f( 'mak_people_lb' ) ); ?></span>
        <div class="row">
          <?php $i = 0; foreach ( $people as $r ) : $i++; $d = ( $i - 1 ) % 4;
            $u = $img_url( isset( $r['ppl_ph'] ) ? $r['ppl_ph'] : 0, isset( $r['_url'] ) ? $r['_url'] : '', 'large' ); ?>
          <figure class="alb-rv<?php echo $d ? ' alb-d' . $d : ''; ?>">
            <div class="ph"><?php if ( $u ) : ?><img src="<?php echo esc_url( $u ); ?>" alt="<?php echo esc_attr( isset( $r['ppl_role'] ) ? $r['ppl_role'] : '' ); ?>" loading="lazy"><?php endif; ?></div>
            <figcaption>
              <span class="role"><?php echo esc_html( isset( $r['ppl_role'] ) ? $r['ppl_role'] : '' ); ?></span>
              <span class="line"><?php echo esc_html( isset( $r['ppl_line'] ) ? $r['ppl_line'] : '' ); ?></span>
            </figcaption>
          </figure>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- 3. EUROPE : l'index du carnet -->
  <section class="alb-europe alb-dark" id="alb-europe">
    <div class="alb-folio" aria-hidden="true">ii</div>
    <?php if ( $ribbon ) : ?>
    <div class="alb-ribbon" aria-hidden="true"><div class="track"><?php for ( $k = 0; $k < 2; $k++ ) { foreach ( $ribbon as $place ) echo '<span>' . esc_html( $place ) . '</span>'; } ?></div></div>
    <?php endif; ?>
    <div class="alb-wrap">
      <div class="head">
        <div class="alb-rv">
          <div class="alb-chap"><span class="rn">II</span><span class="lb"><?php echo esc_html( $f( 'eur_lb' ) ); ?></span></div>
          <h2><?php echo esc_html( $f( 'eur_title' ) ); ?> <em><?php echo esc_html( $f( 'eur_title_em' ) ); ?></em></h2>
        </div>
        <p class="alb-lede alb-rv alb-d2"><?php echo esc_html( $f( 'eur_intro' ) ); ?></p>
      </div>
      <div class="alb-atlas">
        <div class="alb-index">
          <?php $i = 0; $panes = array(); foreach ( $eur as $r ) : $i++; $d = ( $i - 1 ) % 3;
            $n  = isset( $r['eur_n'] ) ? $r['eur_n'] : '';
            $c  = isset( $r['eur_c'] ) ? $r['eur_c'] : '';
            if ( '' === trim( (string) $c ) && '' !== $n ) $c = strtoupper( substr( $n, 0, 2 ) );
            $u  = $img_url( isset( $r['eur_ph'] ) ? $r['eur_ph'] : 0, isset( $r['_url'] ) ? $r['_url'] : '', 'large' );
            $panes[] = array( 'n' => $n, 'c' => $c, 'u' => $u );
          ?>
          <div class="row alb-rv<?php echo $d ? ' alb-d' . $d : ''; ?><?php echo 1 === $i ? ' is-on' : ''; ?>" data-alb-pane="<?php echo $i; ?>">
            <div class="no"><?php echo esc_html( $roman( $i ) ); ?></div>
            <div class="tx">
              <h3><?php echo esc_html( $n ); ?></h3>
              <p><?php echo esc_html( isset( $r['eur_d'] ) ? $r['eur_d'] : '' ); ?></p>
            </div>
            <div class="ph" aria-hidden="true"><?php if ( $u ) : ?><img src="<?php echo esc_url( $u ); ?>" alt="" loading="lazy"><?php else : echo esc_html( $c ); endif; ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="alb-pane alb-rv alb-d1" aria-hidden="true">
          <?php $i = 0; foreach ( $panes as $p ) : $i++; ?>
          <figure class="<?php echo 1 === $i ? 'is-on' : ''; ?>" data-alb-pane="<?php echo $i; ?>">
            <?php if ( $p['u'] ) : ?>
            <img src="<?php echo esc_url( $p['u'] ); ?>" alt="" loading="lazy">
            <?php else : ?>
            <div class="plate"><span class="c"><?php echo esc_html( $p['c'] ); ?></span><span class="n"><?php echo esc_html( $p['n'] ); ?></span></div>
            <?php endif; ?>
            <figcaption><span class="c"><?php echo esc_html( $p['c'] ); ?></span><?php echo esc_html( $p['n'] ); ?></figcaption>
          </figure>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if ( $f( 'eur_foot' ) ) : ?>
      <p class="foot alb-rv"><?php echo esc_html( $f( 'eur_foot' ) ); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- 4. BEHIND CLOSED DOORS : bande photo pleine largeur -->
  <section class="alb-doors" id="alb-doors">
    <div class="alb-band-media" aria-hidden="true">
      <?php if ( $dor_url ) : ?><img src="<?php echo esc_url( $dor_url ); ?>" alt="" loading="lazy" onerror="this.style.display='none'"><?php endif; ?>
    </div>
    <div class="alb-band-scrim" aria-hidden="true"></div>
    <div class="alb-folio" aria-hidden="true">iii</div>
    <div class="alb-narrow copy">
      <div class="alb-chap alb-rv"><span class="rn">III</span><span class="lb"><?php echo esc_html( $f( 'dor_lb' ) ); ?></span></div>
      <h2 class="alb-rv alb-d1"><?php echo esc_html( $f( 'dor_title' ) ); ?> <em><?php echo esc_html( $f( 'dor_title_em' ) ); ?></em></h2>
      <p class="lead alb-rv alb-d2"><?php echo esc_html( $f( 'dor_p1' ) ); ?></p>
      <p class="alb-rv alb-d3"><?php echo esc_html( $f( 'dor_p2' ) ); ?></p>
      <?php if ( $f( 'dor_seal' ) ) : ?>
      <div class="alb-seal alb-rv alb-d4" aria-hidden="true"><?php echo esc_html( $f( 'dor_seal' ) ); ?></div>
      <?php endif; ?>
    </div>
  </section>

  <!-- 5. PAGES DU CARNET -->
  <?php if ( $pages ) : ?>
  <section class="alb-pages alb-ivory">
    <div class="alb-wrap">
      <div class="head alb-rv">
        <span class="alb-kicker left"><?php echo esc_html( $f( 'dor_pages_lb' ) ); ?></span>
        <h2><?php echo esc_html( $f( 'dor_pages_t' ) ); ?> <em><?php echo esc_html( $f( 'dor_pages_te' ) ); ?></em></h2>
      </div>
      <div class="list">
        <?php $i = 0; foreach ( $pages as $r ) : $i++; $d = ( $i - 1 ) % 3;
          $u = $img_url( isset( $r['dor_pp'] ) ? $r['dor_pp'] : 0, isset( $r['_url'] ) ? $r['_url'] : '', 'large' ); ?>
        <article class="item alb-rv<?php echo $d ? ' alb-d' . $d : ''; ?>">
          <div class="ph"><?php if ( $u ) : ?><img src="<?php echo esc_url( $u ); ?>" alt="<?php echo esc_attr( isset( $r['dor_ph'] ) ? $r['dor_ph'] : '' ); ?>" loading="lazy"><?php endif; ?><span class="no"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></span></div>
          <h3><?php echo esc_html( isset( $r['dor_ph'] ) ? $r['dor_ph'] : '' ); ?></h3>
          <p><?php echo esc_html( isset( $r['dor_pd'] ) ? $r['dor_pd'] : '' ); ?></p>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- 6. COMMENT NOUS OUVRONS LES PORTES -->
  <section class="alb-way alb-wine" id="alb-way">
    <div class="alb-folio" aria-hidden="true">iv</div>
    <div class="alb-wrap">
      <div class="head alb-rv">
        <div class="alb-chap"><span class="rn">IV</span><span class="lb"><?php echo esc_html( $f( 'way_lb' ) ); ?></span></div>
        <h2><?php echo esc_html( $f( 'way_title' ) ); ?> <em><?php echo esc_html( $f( 'way_title_em' ) ); ?></em></h2>
        <p class="alb-lede"><?php echo esc_html( $f( 'way_intro' ) ); ?></p>
      </div>
      <div class="list">
        <?php $i = 0; foreach ( $way as $r ) : $i++; $d = ( $i - 1 ) % 3; ?>
        <div class="item alb-rv<?php echo $d ? ' alb-d' . $d : ''; ?>">
          <span class="no"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></span>
          <h3><?php echo esc_html( isset( $r['way_h'] ) ? $r['way_h'] : '' ); ?></h3>
          <p><?php echo esc_html( isset( $r['way_d'] ) ? $r['way_d'] : '' ); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- 7. CITATION PLEINE LARGEUR -->
  <?php if ( $f( 'quo_txt' ) ) : ?>
  <section class="alb-quote">
    <div class="alb-band-media" aria-hidden="true">
      <?php if ( $quo_url ) : ?><img src="<?php echo esc_url( $quo_url ); ?>" alt="" loading="lazy" onerror="this.style.display='none'"><?php endif; ?>
    </div>
    <div class="alb-band-scrim" aria-hidden="true"></div>
    <div class="alb-narrow">
      <p class="q alb-rv"><?php echo esc_html( $f( 'quo_txt' ) ); ?></p>
      <?php if ( $f( 'quo_by' ) ) : ?>
      <span class="alb-kicker alb-rv alb-d2"><?php echo esc_html( $f( 'quo_by' ) ); ?></span>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- 8. UN MOT SUR LA DISCRÉTION -->
  <?php if ( $f( 'note_txt' ) ) : ?>
  <section class="alb-note alb-ivory" id="alb-note">
    <div class="alb-narrow">
      <div class="box alb-rv">
        <div class="alb-chap"><span class="rn">V</span><span class="lb"><?php echo esc_html( $f( 'note_lb' ) ); ?></span></div>
        <p class="q"><?php echo esc_html( $f( 'note_txt' ) ); ?></p>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- 9. CLÔTURE : ouvrir le carnet -->
  <section class="alb-cta">
    <div class="alb-band-media" aria-hidden="true">
      <?php if ( $cta_url ) : ?><img src="<?php echo esc_url( $cta_url ); ?>" alt="" loading="lazy" onerror="this.style.display='none'"><?php endif; ?>
    </div>
    <div class="alb-band-scrim deep" aria-hidden="true"></div>
    <div class="alb-cover" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
    <?php if ( $f( 'cta_lb' ) ) : ?>
    <span class="alb-kicker alb-rv"><?php echo esc_html( $f( 'cta_lb' ) ); ?></span>
    <?php endif; ?>
    <h2 class="alb-rv alb-d1"><?php echo esc_html( $f( 'cta_title' ) ); ?> <em><?php echo esc_html( $f( 'cta_title_em' ) ); ?></em></h2>
    <div class="rule alb-rv alb-d2" aria-hidden="true"></div>
    <p class="alb-rv alb-d3"><?php echo esc_html( $f( 'cta_p' ) ); ?></p>
    <div class="actions alb-rv alb-d4">
      <?php if ( $f( 'cta_btn_l' ) ) : ?>
      <a class="alb-btn" href="<?php echo $btn_url; ?>"><?php echo esc_html( $f( 'cta_btn_l' ) ); ?></a>
      <?php endif; ?>
      <?php if ( $f( 'cta_link_l' ) && $alt_url ) : ?>
      <a class="alt" href="<?php echo esc_url( $alt_url ); ?>"><?php echo esc_html( $f( 'cta_link_l' ) ); ?></a>
      <?php endif; ?>
    </div>
  </section>

</div>
<script>
(function(){
  "use strict";
  var root = document.querySelector('.alb-root');
  if (!root || root.dataset.albInit) return;
  root.dataset.albInit = '1';
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var inEditor = document.body && document.body.classList.contains('block-editor-page');
  if ('IntersectionObserver' in window && !reduce && !inEditor){
    root.classList.add('alb-anim');
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (e.isIntersecting){ e.target.classList.add('alb-in'); io.unobserve(e.target); }
      });
    },{threshold:.12,rootMargin:'0px 0px -40px 0px'});
    root.querySelectorAll('.alb-rv, .alb-cover').forEach(function(el){ io.observe(el); });
  }
  var vid = root.querySelector('.alb-hero-media video');
  if (vid && reduce){ vid.removeAttribute('autoplay'); vid.pause(); }
  /* index Europe : la photo de droite suit la ligne survolee */
  var rows = root.querySelectorAll('.alb-index .row[data-alb-pane]');
  var panes = root.querySelectorAll('.alb-pane figure[data-alb-pane]');
  function show(n){
    rows.forEach(function(r){ r.classList.toggle('is-on', r.getAttribute('data-alb-pane') === n); });
    panes.forEach(function(p){ p.classList.toggle('is-on', p.getAttribute('data-alb-pane') === n); });
  }
  rows.forEach(function(r){
    r.addEventListener('mouseenter', function(){ show(r.getAttribute('data-alb-pane')); });
    r.addEventListener('focusin', function(){ show(r.getAttribute('data-alb-pane')); });
  });
})();
</script>
	<?php
	echo ob_get_clean();
}

/* ================================================================== *
 * BLOC OUR STORY — page anniversaire « 20 ans »
 * -----------------------------------------------------------------
 * Un SEUL bloc pour toute la page : le module d'avis est rendu à
 * l'intérieur via un champ shortcode (plus besoin de découper la
 * page en trois blocs HTML).
 * Conventions : clés ofld_… / onglets otab_…  (name = substr($key,5))
 * ================================================================== */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) return;
	acf_register_block_type( array(
		'name'            => 'aav-our-story',
		'title'           => __( 'AAV — Page : Our Story (20 ans)', 'aav-lb' ),
		'description'     => __( 'Éditable : textes, photos, jalons, équipe. Structure figée.', 'aav-lb' ),
		'category'        => 'formatting',
		'icon'            => 'book-alt',
		'keywords'        => array( 'aav', 'our story', 'about', '20 ans', 'anniversaire' ),
		'mode'            => 'edit',
		'api_version'       => 3,
		'acf_block_version' => 3,
		'render_preview'    => false,
		'supports'        => array( 'align' => false, 'multiple' => false, 'jsx' => true, 'mode' => false ),
		'render_callback' => 'aav_lb_render_our_story',
	) );
} );

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$T = function ( $key, $label, $default = '', $type = 'text', $instr = '' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => $type, 'default_value' => $default, 'instructions' => $instr );
	};
	$tab = function ( $key, $label ) { return array( 'key' => $key, 'label' => $label, 'type' => 'accordion', 'open' => 0, 'multi_expand' => 1 ); };
	$img = function ( $key, $label, $instr = 'Vide = la photo actuelle de la page reste affichée.' ) {
		return array( 'key' => $key, 'label' => $label, 'name' => substr( $key, 5 ), 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all', 'instructions' => $instr );
	};

	$f = array();

	/* ---- Héro ---- */
	$f[] = $tab( 'otab_hero', 'Héro' );
	$f[] = $T( 'ofld_hero_video', 'Vidéo de fond (URL .mp4)', 'https://www.aavluxurytravel.com/wp-content/uploads/2026/07/AAV-20-YEARS_gwr_video_v1.mp4', 'text', 'MP4 H.264, 1080p, boucle 10–20 s, sans son, idéalement moins de 10 Mo.' );
	$f[] = $img( 'ofld_hero_photo', 'Image de fond', 'Affichée pendant le chargement de la vidéo et en secours.' );
	$f[] = $T( 'ofld_hero_years', 'Années', '2006 — 2026' );
	$f[] = $T( 'ofld_hero_title', 'Titre', 'Celebrating twenty years' );
	$f[] = $T( 'ofld_hero_title_em', 'Titre — italique doré', 'of exceptional journeys' );
	$f[] = $T( 'ofld_hero_sub', 'Accroche', 'For two decades, Académie des Arts de Vivre has been designing highly personalized journeys throughout France and neighbouring Europe — England, Spain, Portugal, Italy and Switzerland — combining local expertise, privileged access, and a deep understanding of what makes travel truly meaningful.', 'textarea' );
	$f[] = $T( 'ofld_hero_hint', 'Mention de défilement', 'Discover' );
	$f[] = $T( 'ofld_spine_top', 'Rail — texte du haut', 'Our Story' );
	$f[] = $T( 'ofld_spine_bot', 'Rail — texte du bas', '2006 — 2026' );

	/* ---- I. Philosophie ---- */
	$f[] = $tab( 'otab_phi', 'I — Philosophie' );
	$f[] = $T( 'ofld_phi_lb', 'Libellé du chapitre', 'Our philosophy' );
	$f[] = $T( 'ofld_phi_lead', 'Grande citation', 'Luxury is not about excess. It is about', 'textarea' );
	$f[] = $T( 'ofld_phi_lead_em', 'Citation — mot en doré', 'authenticity' );
	$f[] = $T( 'ofld_phi_lead_end', 'Citation — suite après le mot doré', ', meaningful encounters, and details entirely your own.', 'textarea' );
	$f[] = $T( 'ofld_phi_p1', 'Paragraphe 1', 'At AAV, we believe travel should be designed around people. Every itinerary begins with a conversation and is personally shaped by one of our travel specialists, drawing on years of expertise and long-standing relationships across Europe.', 'textarea' );
	$f[] = $T( 'ofld_phi_p2', 'Paragraphe 2', 'Twenty years of trusted relationships and destination knowledge — across France above all, and throughout neighbouring Europe — let us create experiences that go far beyond traditional travel.', 'textarea' );
	$f[] = $T( 'ofld_phi_sign', 'Signature italique', 'No two journeys are ever the same.', 'textarea' );

	/* ---- II. Timeline ---- */
	$f[] = $tab( 'otab_tl', 'II — Chronologie' );
	$f[] = $T( 'ofld_tl_lb', 'Libellé du chapitre', 'Two decades' );
	$f[] = $T( 'ofld_tl_title', 'Titre', 'A story written' );
	$f[] = $T( 'ofld_tl_title_em', 'Titre — italique doré', 'over twenty years' );
	$f[] = array( 'key' => 'ofld_tl_items', 'label' => 'Jalons', 'name' => 'tl_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un jalon', 'instructions' => 'Vide = garder les 5 jalons par défaut. L\'alternance gauche/droite est automatique.', 'sub_fields' => array(
		$T( 'ofld_tl_y', 'Année', '' ),
		$T( 'ofld_tl_h', 'Titre', '' ),
		$T( 'ofld_tl_d', 'Description', '', 'textarea' ),
	) );

	/* ---- III. Équipe ---- */
	$f[] = $tab( 'otab_team', 'III — Équipe' );
	$f[] = $T( 'ofld_team_lb', 'Libellé du chapitre', 'The team' );
	$f[] = $T( 'ofld_team_title', 'Titre', 'The people behind' );
	$f[] = $T( 'ofld_team_title_em', 'Titre — italique doré', 'every journey' );
	$f[] = $T( 'ofld_team_lede', 'Introduction', 'Boston, London, Paris — a small team of specialists, each devoted to a single craft: yours. Hover a portrait to meet them.', 'textarea' );
	$f[] = array( 'key' => 'ofld_team_items', 'label' => 'Membres', 'name' => 'team_items', 'type' => 'repeater', 'layout' => 'block', 'button_label' => 'Ajouter un membre', 'instructions' => 'Vide = garder l\'équipe par défaut. Portraits recommandés en 3:4 ; le noir &amp; blanc est appliqué automatiquement.', 'sub_fields' => array(
		$img( 'ofld_team_photo', 'Portrait' ),
		$T( 'ofld_team_ini', 'Initiales (si pas de photo)', '' ),
		$T( 'ofld_team_n', 'Prénom', '' ),
		$T( 'ofld_team_r', 'Rôle', '' ),
	) );

	/* ---- IV. Approche ---- */
	$f[] = $tab( 'otab_ap', 'IV — Approche' );
	$f[] = $img( 'ofld_ap_photo', 'Photo (moitié gauche)' );
	$f[] = $T( 'ofld_ap_lb', 'Libellé du chapitre', 'A personal approach' );
	$f[] = $T( 'ofld_ap_title', 'Titre', 'Travel begins' );
	$f[] = $T( 'ofld_ap_title_em', 'Titre — italique doré', 'with a conversation' );
	$f[] = $T( 'ofld_ap_p1', 'Paragraphe 1', 'When you contact AAV, you speak directly with a travel specialist. We do not operate through anonymous reservation teams or call centers.', 'textarea' );
	$f[] = $T( 'ofld_ap_p2', 'Paragraphe 2', 'From your first conversation to your return home, your journey is guided by a dedicated advisor who takes the time to understand your interests, preferences and aspirations.', 'textarea' );
	$f[] = $T( 'ofld_ap_btn_l', 'Bouton — libellé', 'Begin the conversation' );
	$f[] = $T( 'ofld_ap_btn_u', 'Bouton — lien', '/tailor-my-trip/' );

	/* ---- V. Réseau ---- */
	$f[] = $tab( 'otab_net', 'V — Réseau' );
	$f[] = $T( 'ofld_net_lb', 'Libellé du chapitre', 'Our network' );
	$f[] = $T( 'ofld_net_st', 'Grande phrase', 'Enduring relationships with an', 'textarea' );
	$f[] = $T( 'ofld_net_st_em', 'Grande phrase — partie dorée', 'exceptional network' );
	$f[] = $T( 'ofld_net_st_end', 'Grande phrase — fin', '.', 'text' );
	$f[] = $T( 'ofld_net_lede', 'Paragraphe', 'Bilingual guides, private chauffeurs, hoteliers, chefs and local experts across France and neighbouring Europe — many of these trusted partners have been part of the AAV adventure for nearly two decades.', 'textarea' );
	$f[] = $T( 'ofld_net_roles', 'Métiers (séparés par des virgules)', 'Guides, Chauffeurs, Hoteliers, Chefs, Local Experts' );

	/* ---- VI. Présence ---- */
	$f[] = $tab( 'otab_pre', 'VI — Présence' );
	$f[] = $T( 'ofld_pre_lb', 'Libellé du chapitre', 'International presence' );
	$f[] = $T( 'ofld_pre_title', 'Titre', 'Three cities,' );
	$f[] = $T( 'ofld_pre_title_em', 'Titre — italique doré', 'one conversation' );
	$f[] = $T( 'ofld_pre_lede', 'Paragraphe', 'International perspective paired with intimate destination knowledge — serving travelers the world over while remaining deeply connected to the places we know best.', 'textarea' );
	$f[] = array( 'key' => 'ofld_pre_offices', 'label' => 'Bureaux (horloges)', 'name' => 'pre_offices', 'type' => 'repeater', 'layout' => 'table', 'button_label' => 'Ajouter un bureau', 'instructions' => 'Vide = Boston, Londres, Paris. Le fuseau doit être un identifiant valide (ex. Europe/Paris). La carte reste dessinée sur ces trois villes.', 'sub_fields' => array(
		$T( 'ofld_pre_c', 'Ville', '' ),
		$T( 'ofld_pre_co', 'Coordonnées', '' ),
		$T( 'ofld_pre_tz', 'Fuseau horaire', '' ),
	) );

	/* ---- Avis ---- */
	$f[] = $tab( 'otab_rev', 'Avis clients' );
	$f[] = $T( 'ofld_rev_sc', 'Shortcode du module d\'avis', '[aav_home_testimonials url="/client-stories/" layout="grid"]', 'text', 'Laisser vide pour ne pas afficher la section avis. Le module conserve son propre design.' );

	/* ---- VII. Clôture ---- */
	$f[] = $tab( 'otab_cl', 'VII — Clôture' );
	$f[] = $T( 'ofld_cl_lb', 'Libellé du chapitre', 'More than a journey' );
	$f[] = $T( 'ofld_cl_title', 'Titre', 'Twenty years on, our mission' );
	$f[] = $T( 'ofld_cl_title_em', 'Titre — italique doré', 'remains unchanged' );
	$f[] = $T( 'ofld_cl_p', 'Paragraphe', 'To create meaningful experiences, open doors to extraordinary places, and help our travelers discover Europe in a way that feels deeply personal.', 'textarea' );
	$f[] = $T( 'ofld_cl_mission', 'Signature italique', 'The art of living, shared.' );
	$f[] = $T( 'ofld_cl_btn_l', 'Bouton — libellé', 'Tailor my trip' );
	$f[] = $T( 'ofld_cl_btn_u', 'Bouton — lien', '/tailor-my-trip/' );
	$f[] = $T( 'ofld_cl_years', 'Années en clôture', '2006 — 2026' );

	acf_add_local_field_group( array(
		'key'      => 'group_aav_lb_our_story',
		'title'    => 'AAV — Page : Our Story',
		'fields'   => $f,
		'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/aav-our-story' ) ) ),
	) );
} );

function aav_lb_render_our_story( $block, $content = '', $is_preview = false ) {

	if ( $is_preview || is_admin() ) {
		aav_lb_editor_placeholder( 'AAV — Page : Our Story' );
		return;
	}

	/* Surcharge HTML du modele : si presente, elle remplace le rendu dynamique */
	if ( aav_lb_maybe_render_override( 'our-story' ) ) return;

	$f    = function ( $n, $d = '' ) { $v = get_field( $n ); return ( $v === '' || $v === null || $v === false ) ? $d : $v; };
	$rows = function ( $n, $fb ) { $v = get_field( $n ); return ( is_array( $v ) && count( $v ) ) ? $v : $fb; };

	$P  = 'https://www.aavluxurytravel.com/wp-content/uploads/2026/07/';
	$dp = array(
		'mt'  => $P . 'Marie-Therese-de-Willermin.png',
		'er'  => $P . 'Eric-de-Willermin-1.png',
		'ma'  => $P . 'Marion-de-Willermin.png',
		'be'  => $P . 'Beatrice-Thouveny-3.png',
		'jb'  => $P . 'Jean-Baptiste-Biolay-1.png',
		'st'  => $P . 'Stan-Thouveny-2.png',
		'ap'  => $P . 'Eric-01.png',
	);

	/* URL d'une photo : ID de champ, sinon URL par défaut résolue en ID */
	$photo_url = function ( $id, $default_url = '' ) {
		$u = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
		if ( ! $u && $default_url ) { $d = aav_lb_url_to_id( $default_url ); $u = $d ? wp_get_attachment_image_url( $d, 'full' ) : $default_url; }
		return $u;
	};

	$def_tl = array(
		array( 'tl_y' => '2006', 'tl_h' => 'The beginning',        'tl_d' => 'Académie des Arts de Vivre is founded in Paris, born of a conviction that travel should be personal, cultured and quietly exceptional.' ),
		array( 'tl_y' => '2011', 'tl_h' => 'Across the Atlantic',  'tl_d' => 'A Boston presence brings AAV closer to the discerning American travelers who would become the heart of the maison.' ),
		array( 'tl_y' => '2016', 'tl_h' => 'A network deepens',    'tl_d' => 'Ten years of cultivated relationships open doors unknown to the tourist industry — private châteaux, ateliers, and cellars.' ),
		array( 'tl_y' => '2021', 'tl_h' => 'Three cities, one team','tl_d' => 'Boston, London and Paris now work as one — international perspective paired with intimate local knowledge.' ),
		array( 'tl_y' => '2026', 'tl_h' => 'Twenty years on',      'tl_d' => 'Two decades of exceptional journeys — and a mission that remains, at its heart, entirely unchanged.' ),
	);
	$def_team = array(
		array( 'team_photo' => 0, 'u' => $dp['mt'], 'team_ini' => 'M-T', 'team_n' => 'Marie-Thérèse', 'team_r' => 'Founder &amp; Senior Curator' ),
		array( 'team_photo' => 0, 'u' => $dp['er'], 'team_ini' => 'É',   'team_n' => 'Éric',          'team_r' => 'Chief Executive &amp; Specialist' ),
		array( 'team_photo' => 0, 'u' => $dp['ma'], 'team_ini' => 'M',   'team_n' => 'Marion',        'team_r' => 'Operations &amp; Client Experience' ),
		array( 'team_photo' => 0, 'u' => $dp['be'], 'team_ini' => 'B',   'team_n' => 'Béatrice',      'team_r' => 'Luxury Travel Advisor' ),
		array( 'team_photo' => 0, 'u' => $dp['jb'], 'team_ini' => 'J-B', 'team_n' => 'Jean-Baptiste', 'team_r' => 'Digital Experience' ),
		array( 'team_photo' => 0, 'u' => $dp['st'], 'team_ini' => 'S',   'team_n' => 'Stan',          'team_r' => 'Strategy &amp; Finance' ),
	);
	$def_off = array(
		array( 'pre_c' => 'Boston', 'pre_co' => '42.36° N · 71.06° W', 'pre_tz' => 'America/New_York' ),
		array( 'pre_c' => 'London', 'pre_co' => '51.51° N · 0.13° W',  'pre_tz' => 'Europe/London' ),
		array( 'pre_c' => 'Paris',  'pre_co' => '48.86° N · 2.35° E',  'pre_tz' => 'Europe/Paris' ),
	);

	$tl   = $rows( 'tl_items', $def_tl );
	$team = $rows( 'team_items', $def_team );
	$off  = $rows( 'pre_offices', $def_off );

	$video = trim( (string) $f( 'hero_video' ) );
	$hero_url = $photo_url( (int) get_field( 'hero_photo' ), '' );
	$ap_url   = $photo_url( (int) get_field( 'ap_photo' ), $dp['ap'] );

	$roles = array_filter( array_map( 'trim', explode( ',', (string) $f( 'net_roles' ) ) ) );

	$css = aav_lb_css( 'our-story' );
	ob_start();
	if ( $css ) echo '<style>' . $css . '</style>';
	?>
<div class="aos-root">

  <div class="aos-spine" aria-hidden="true">
    <span class="cap top"><?php echo esc_html( $f( 'spine_top' ) ); ?></span>
    <span class="track"><span class="fill" id="aosFill"></span></span>
    <span class="cap"><?php echo esc_html( $f( 'spine_bot' ) ); ?></span>
  </div>

  <!-- 1. HÉRO -->
  <section class="aos-hero" id="our-story">
    <div class="aos-hero-media" aria-hidden="true">
      <?php if ( $hero_url ) : ?>
      <img class="aos-hero-still" src="<?php echo esc_url( $hero_url ); ?>" alt="" loading="eager" onerror="this.style.display='none'">
      <?php endif; ?>
      <?php if ( $video ) : ?>
      <video class="aos-hero-vid" autoplay muted loop playsinline preload="metadata"<?php echo $hero_url ? ' poster="' . esc_url( $hero_url ) . '"' : ''; ?>>
        <source src="<?php echo esc_url( $video ); ?>" type="video/mp4">
      </video>
      <?php endif; ?>
    </div>
    <div class="aos-hero-scrim" aria-hidden="true"></div>
    <div class="aos-hero-xx" aria-hidden="true">XX</div>
    <div class="aos-hero-years aos-rv"><?php echo esc_html( $f( 'hero_years' ) ); ?></div>
    <h1 class="aos-rv"><?php echo esc_html( $f( 'hero_title' ) ); ?> <em><?php echo esc_html( $f( 'hero_title_em' ) ); ?></em></h1>
    <div class="aos-hero-rule aos-rv" aria-hidden="true"></div>
    <p class="aos-rv aos-d2"><?php echo esc_html( $f( 'hero_sub' ) ); ?></p>
    <div class="aos-scroll-hint aos-rv aos-d4"><?php echo esc_html( $f( 'hero_hint' ) ); ?></div>
  </section>

  <!-- 2. PHILOSOPHIE -->
  <section class="aos-philosophy">
    <div class="aos-ghost" aria-hidden="true">20</div>
    <div class="aos-phi-inner">
      <div class="aos-chap aos-rv"><span class="rn">I</span><span class="lb"><?php echo esc_html( $f( 'phi_lb' ) ); ?></span></div>
      <p class="aos-phi-lead aos-rv aos-d1"><?php echo esc_html( $f( 'phi_lead' ) ); ?> <span class="aos-em"><?php echo esc_html( $f( 'phi_lead_em' ) ); ?></span><?php echo esc_html( $f( 'phi_lead_end' ) ); ?></p>
      <div class="aos-phi-foot">
        <div class="aos-rv aos-d2">
          <p><?php echo esc_html( $f( 'phi_p1' ) ); ?></p>
          <p><?php echo esc_html( $f( 'phi_p2' ) ); ?></p>
        </div>
        <div class="aos-sign aos-rv aos-d3"><?php echo nl2br( esc_html( $f( 'phi_sign' ) ) ); ?></div>
      </div>
    </div>
  </section>

  <!-- 3. CHRONOLOGIE -->
  <section class="aos-timeline">
    <div class="aos-tl-head aos-rv">
      <div class="aos-chap"><span class="rn">II</span><span class="lb"><?php echo esc_html( $f( 'tl_lb' ) ); ?></span></div>
      <h2><?php echo esc_html( $f( 'tl_title' ) ); ?> <em><?php echo esc_html( $f( 'tl_title_em' ) ); ?></em></h2>
    </div>
    <div class="aos-tl-wrap">
      <span class="aos-tl-line" aria-hidden="true"></span>
      <span class="aos-tl-fill" id="aosTlFill" aria-hidden="true"></span>
      <?php $i = 0; foreach ( $tl as $r ) : $i++; $side = ( $i % 2 ) ? 'l' : 'r'; ?>
      <div class="aos-milestone <?php echo $side; ?> aos-rv">
        <?php if ( 'l' === $side ) : ?>
        <div class="yr"><?php echo esc_html( isset( $r['tl_y'] ) ? $r['tl_y'] : '' ); ?></div>
        <?php else : ?>
        <div class="side"><div class="yr"><?php echo esc_html( isset( $r['tl_y'] ) ? $r['tl_y'] : '' ); ?></div></div>
        <?php endif; ?>
        <div class="body">
          <h4><?php echo esc_html( isset( $r['tl_h'] ) ? $r['tl_h'] : '' ); ?></h4>
          <p><?php echo esc_html( isset( $r['tl_d'] ) ? $r['tl_d'] : '' ); ?></p>
        </div>
        <span class="dot" aria-hidden="true"></span>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 4. ÉQUIPE -->
  <section class="aos-team aos-dark">
    <div class="aos-team-head aos-rv">
      <div>
        <div class="aos-chap"><span class="rn">III</span><span class="lb"><?php echo esc_html( $f( 'team_lb' ) ); ?></span></div>
        <h2><?php echo esc_html( $f( 'team_title' ) ); ?> <em><?php echo esc_html( $f( 'team_title_em' ) ); ?></em></h2>
      </div>
      <p class="aos-lede"><?php echo esc_html( $f( 'team_lede' ) ); ?></p>
    </div>
    <div class="aos-team-strip">
      <?php $i = 0; foreach ( $team as $m ) : $i++; $d = ( $i - 1 ) % 4;
        $u = $photo_url( isset( $m['team_photo'] ) ? (int) $m['team_photo'] : 0, isset( $m['u'] ) ? $m['u'] : '' );
        $nm = isset( $m['team_n'] ) ? $m['team_n'] : '';
      ?>
      <div class="aos-member aos-cut<?php echo $d ? ' aos-d' . $d : ''; ?>">
        <span class="initials"><?php echo esc_html( isset( $m['team_ini'] ) ? $m['team_ini'] : '' ); ?></span>
        <?php if ( $u ) : ?><img src="<?php echo esc_url( $u ); ?>" alt="<?php echo esc_attr( $nm ); ?>" loading="lazy" onerror="this.style.display='none'"><?php endif; ?>
        <div class="cap"><h3><?php echo esc_html( $nm ); ?></h3><div class="role"><?php echo wp_kses_post( isset( $m['team_r'] ) ? $m['team_r'] : '' ); ?></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 5. APPROCHE -->
  <section class="aos-approach aos-dark">
    <div class="aos-ap-visual aos-rv">
      <?php if ( $ap_url ) : ?><img src="<?php echo esc_url( $ap_url ); ?>" alt="" loading="lazy" onerror="this.style.display='none'"><?php endif; ?>
    </div>
    <div class="aos-ap-copy">
      <div class="aos-chap aos-rv"><span class="rn">IV</span><span class="lb"><?php echo esc_html( $f( 'ap_lb' ) ); ?></span></div>
      <h2 class="aos-rv aos-d1"><?php echo esc_html( $f( 'ap_title' ) ); ?> <em><?php echo esc_html( $f( 'ap_title_em' ) ); ?></em></h2>
      <div class="aos-lede aos-rv aos-d2">
        <p><?php echo esc_html( $f( 'ap_p1' ) ); ?></p>
        <p><?php echo esc_html( $f( 'ap_p2' ) ); ?></p>
      </div>
      <?php if ( $f( 'ap_btn_l' ) ) : ?>
      <a class="aos-btn aos-rv aos-d3" href="<?php echo esc_url( $f( 'ap_btn_u' ) ); ?>"><?php echo esc_html( $f( 'ap_btn_l' ) ); ?></a>
      <?php endif; ?>
    </div>
  </section>

  <!-- 6. RÉSEAU -->
  <section class="aos-network">
    <div class="aos-wrap aos-rv">
      <div class="aos-chap"><span class="rn">V</span><span class="lb"><?php echo esc_html( $f( 'net_lb' ) ); ?></span></div>
      <p class="aos-statement"><?php echo esc_html( $f( 'net_st' ) ); ?> <span class="aos-em"><?php echo esc_html( $f( 'net_st_em' ) ); ?></span><?php echo esc_html( $f( 'net_st_end' ) ); ?></p>
      <p class="aos-lede"><?php echo esc_html( $f( 'net_lede' ) ); ?></p>
      <?php if ( $roles ) : ?>
      <div class="aos-net-roles"><?php foreach ( $roles as $r ) echo '<span>' . esc_html( $r ) . '</span>'; ?></div>
      <?php endif; ?>
    </div>
  </section>

  <!-- 7. PRÉSENCE -->
  <section class="aos-presence aos-dark">
    <div class="aos-presence-head aos-rv">
      <div class="aos-chap"><span class="rn">VI</span><span class="lb"><?php echo esc_html( $f( 'pre_lb' ) ); ?></span></div>
      <h2><?php echo esc_html( $f( 'pre_title' ) ); ?> <em><?php echo esc_html( $f( 'pre_title_em' ) ); ?></em></h2>
      <p class="aos-lede"><?php echo esc_html( $f( 'pre_lede' ) ); ?></p>
    </div>
    <div class="aos-map-frame aos-rv aos-d2">
      <svg viewBox="0 0 960 420" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Boston, London and Paris">
        <defs><radialGradient id="aosGlow" cx="50%" cy="45%" r="60%"><stop offset="0%" stop-color="#cfb684" stop-opacity=".07"/><stop offset="100%" stop-color="#cfb684" stop-opacity="0"/></radialGradient></defs>
        <rect width="960" height="420" fill="url(#aosGlow)"/>
        <g stroke="#cfb684" stroke-opacity=".16" fill="none" stroke-width="1">
          <ellipse cx="480" cy="210" rx="430" ry="170"/><ellipse cx="480" cy="210" rx="430" ry="112"/>
          <ellipse cx="480" cy="210" rx="430" ry="52"/><ellipse cx="480" cy="210" rx="160" ry="170"/>
          <ellipse cx="480" cy="210" rx="300" ry="170"/><line x1="480" y1="40" x2="480" y2="380"/><line x1="50" y1="210" x2="910" y2="210"/>
        </g>
        <g stroke="#b08d4f" fill="none" stroke-width="1.3">
          <path class="aos-arc" d="M 240 235 Q 445 95 655 175"/><path class="aos-arc" d="M 655 175 Q 690 205 718 226"/>
        </g>
        <g><circle class="aos-pulse" cx="240" cy="235" r="7" fill="#cfb684"/><circle cx="240" cy="235" r="3.4" fill="#cfb684"/><text x="240" y="272" text-anchor="middle" fill="#f3efe4" font-family="Sailec, Arial, sans-serif" font-size="13" letter-spacing="4">BOSTON</text></g>
        <g><circle class="aos-pulse" cx="655" cy="175" r="7" fill="#cfb684"/><circle cx="655" cy="175" r="3.4" fill="#cfb684"/><text x="655" y="150" text-anchor="middle" fill="#f3efe4" font-family="Sailec, Arial, sans-serif" font-size="13" letter-spacing="4">LONDON</text></g>
        <g><circle class="aos-pulse" cx="718" cy="226" r="7" fill="#cfb684"/><circle cx="718" cy="226" r="3.4" fill="#cfb684"/><text x="718" y="263" text-anchor="middle" fill="#f3efe4" font-family="Sailec, Arial, sans-serif" font-size="13" letter-spacing="4">PARIS</text></g>
      </svg>
    </div>
    <div class="aos-offices">
      <?php $i = 0; foreach ( $off as $o ) : $i++; $d = ( $i - 1 ) % 3; ?>
      <div class="aos-office aos-rv<?php echo $d ? ' aos-d' . $d : ''; ?>">
        <h3><?php echo esc_html( isset( $o['pre_c'] ) ? $o['pre_c'] : '' ); ?></h3>
        <div class="coords"><?php echo esc_html( isset( $o['pre_co'] ) ? $o['pre_co'] : '' ); ?></div>
        <div class="time" data-tz="<?php echo esc_attr( isset( $o['pre_tz'] ) ? $o['pre_tz'] : '' ); ?>">—</div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

</div>
<?php /* Le module d'avis est rendu HORS de .aos-root : la remise à zéro
         des marges du bloc écraserait sinon ses propres espacements. */ ?>
<?php $sc = trim( (string) $f( 'rev_sc' ) ); if ( $sc !== '' ) : ?>
<div class="aos-reviews-zone"><?php echo do_shortcode( wp_kses_post( $sc ) ); ?></div>
<?php endif; ?>
<div class="aos-root">

  <!-- 9. CLÔTURE -->
  <section class="aos-closing" id="contact">
    <div class="ghost" aria-hidden="true">XX</div>
    <div class="chap rv"><span class="rn">VII</span><span class="lb"><?php echo esc_html( $f( 'cl_lb' ) ); ?></span></div>
    <h2 class="rv d1"><?php echo esc_html( $f( 'cl_title' ) ); ?> <em><?php echo esc_html( $f( 'cl_title_em' ) ); ?></em></h2>
    <div class="rule rv d1" aria-hidden="true"></div>
    <p class="rv d2"><?php echo esc_html( $f( 'cl_p' ) ); ?></p>
    <div class="mission rv d3"><?php echo esc_html( $f( 'cl_mission' ) ); ?></div>
    <?php if ( $f( 'cl_btn_l' ) ) : ?>
    <a class="btn rv d4" href="<?php echo esc_url( $f( 'cl_btn_u' ) ); ?>"><?php echo esc_html( $f( 'cl_btn_l' ) ); ?></a>
    <?php endif; ?>
    <div class="years rv d4"><?php echo esc_html( $f( 'cl_years' ) ); ?></div>
  </section>

</div>
<script>
(function(){
  "use strict";
  var roots = document.querySelectorAll('.aos-root');
  if (!roots.length) return;
  if (roots[0].dataset.aosInit) return;
  roots[0].dataset.aosInit = '1';
  if (window.__aosCleanup) { try { window.__aosCleanup(); } catch(e){} }
  var root = roots[0];
  function eachRoot(fn){ Array.prototype.forEach.call(roots, fn); }
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var inEditor = document.body && document.body.classList.contains('block-editor-page');

  if ('IntersectionObserver' in window && !reduce && !inEditor){
    eachRoot(function(r){ r.classList.add('aos-anim'); });
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (e.isIntersecting){ e.target.classList.add('aos-in'); io.unobserve(e.target); }
      });
    },{threshold:.14,rootMargin:'0px 0px -40px 0px'});
    eachRoot(function(r){ r.querySelectorAll('.aos-rv,.aos-cut,.aos-milestone').forEach(function(el){ io.observe(el); }); });
    var cl = document.querySelector('.aos-closing');
    if (cl){
      var io2 = new IntersectionObserver(function(en){
        en.forEach(function(e){ if (e.isIntersecting){ cl.classList.add('seen'); io2.disconnect(); } });
      },{threshold:.2});
      io2.observe(cl);
    }
  } else {
    var cl2 = document.querySelector('.aos-closing'); if (cl2) cl2.classList.add('seen');
  }

  var fill = document.getElementById('aosFill');
  var tlFill = document.getElementById('aosTlFill');
  var tlWrap = document.querySelector('.aos-tl-wrap');
  function onScroll(){
    var docH = document.documentElement.scrollHeight - window.innerHeight;
    var p = docH>0 ? Math.min(1, Math.max(0, window.scrollY/docH)) : 0;
    if (fill) fill.style.height = (p*100)+'%';
    if (tlFill && tlWrap){
      var r = tlWrap.getBoundingClientRect();
      var prog = (window.innerHeight*0.5 - r.top) / r.height;
      tlFill.style.height = (Math.min(1, Math.max(0, prog))*100)+'%';
    }
  }
  if (!reduce){ window.addEventListener('scroll', onScroll, {passive:true}); onScroll(); }

  var vid = document.querySelector('.aos-hero-media video');
  if (vid && reduce){ vid.removeAttribute('autoplay'); vid.pause(); }

  function tick(){
    document.querySelectorAll('.aos-office .time[data-tz]').forEach(function(el){
      var tz = el.getAttribute('data-tz'); if (!tz) { el.textContent=''; return; }
      try{ el.textContent = new Intl.DateTimeFormat('en-GB',{hour:'2-digit',minute:'2-digit',timeZone:tz}).format(new Date()) + ' local time'; }
      catch(err){ el.textContent=''; }
    });
  }
  tick();
  var aosTimer = setInterval(tick, 30000);
  window.__aosCleanup = function(){
    window.removeEventListener('scroll', onScroll);
    clearInterval(aosTimer);
  };
})();
</script>
	<?php
	echo ob_get_clean();
}

/* ================================================================== *
 * STUDIO DE MODÈLES — créer des pages sans réinstaller l'extension
 * -----------------------------------------------------------------
 * Un modèle = un nom + du CSS + du HTML, stocké en base.
 * Chaque modèle devient automatiquement :
 *   - un bloc éditable  (acf/aav-tpl-{slug})
 *   - une composition   (inséreur > AAV — Landing Pages)
 * Les zones éditables se déclarent dans le HTML avec des balises :
 *   {{cle|Libellé|type|valeur par défaut}}
 * Types : text (défaut), textarea, image, url
 * Exemple : <h1>{{titre|Titre principal|text|Bonjour}}</h1>
 * ================================================================== */

define( 'AAV_LB_TPL_OPTION', 'aav_lb_custom_templates' );

/* ---- Lecture / écriture du magasin de modèles ---- */
function aav_lb_tpl_all() {
	$t = get_option( AAV_LB_TPL_OPTION, array() );
	return is_array( $t ) ? $t : array();
}
function aav_lb_tpl_get( $slug ) {
	$all = aav_lb_tpl_all();
	return isset( $all[ $slug ] ) ? $all[ $slug ] : null;
}
function aav_lb_tpl_save( $slug, $data ) {
	$all = aav_lb_tpl_all();
	$all[ $slug ] = $data;
	update_option( AAV_LB_TPL_OPTION, $all, false );
}
function aav_lb_tpl_delete( $slug ) {
	$all = aav_lb_tpl_all();
	unset( $all[ $slug ] );
	update_option( AAV_LB_TPL_OPTION, $all, false );
}

/* ---- Nettoyage du CSS (empêche la fermeture prématurée de <style>) ---- */
function aav_lb_tpl_clean_css( $css ) {
	$css = (string) $css;
	$css = preg_replace( '#</\s*style#i', '', $css );
	$css = str_replace( array( '<script', '</script' ), '', $css );
	return $css;
}

/* ---- Analyse des balises {{cle|Libellé|type|défaut}} ---- */
function aav_lb_tpl_parse( $html ) {
	$fields = array();
	if ( ! preg_match_all( '/\{\{\s*([a-zA-Z0-9_-]+)\s*(?:\|([^|}]*))?(?:\|([^|}]*))?(?:\|([^}]*))?\}\}/', (string) $html, $m, PREG_SET_ORDER ) ) {
		return $fields;
	}
	foreach ( $m as $x ) {
		$key = sanitize_key( $x[1] );
		if ( '' === $key || isset( $fields[ $key ] ) ) continue;
		$label = isset( $x[2] ) ? trim( $x[2] ) : '';
		$type  = isset( $x[3] ) ? strtolower( trim( $x[3] ) ) : '';
		$def   = isset( $x[4] ) ? trim( $x[4] ) : '';
		if ( ! in_array( $type, array( 'text', 'textarea', 'image', 'url' ), true ) ) $type = 'text';
		$fields[ $key ] = array(
			'key'     => $key,
			'label'   => $label !== '' ? $label : ucfirst( str_replace( '_', ' ', $key ) ),
			'type'    => $type,
			'default' => $def,
		);
	}
	return $fields;
}

/* ---- Enregistrement dynamique : un bloc + un groupe de champs par modèle ---- */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_register_block_type' ) ) return;

	foreach ( aav_lb_tpl_all() as $slug => $tpl ) {
		$slug = sanitize_key( $slug );
		if ( '' === $slug ) continue;

		acf_register_block_type( array(
			'name'            => 'aav-tpl-' . $slug,
			'title'           => 'AAV — ' . ( isset( $tpl['name'] ) ? $tpl['name'] : $slug ),
			'description'     => __( 'Modèle créé depuis le Studio de modèles AAV.', 'aav-lb' ),
			'category'        => 'formatting',
			'icon'            => 'layout',
			'keywords'        => array( 'aav', 'modele', $slug ),
			'mode'            => 'edit',
		'api_version'       => 3,
		'acf_block_version' => 3,
		'render_preview'    => false,
			'supports'        => array( 'align' => false, 'multiple' => false, 'jsx' => true, 'mode' => false ),
			'render_callback' => 'aav_lb_render_custom_template',
		) );

		if ( ! function_exists( 'acf_add_local_field_group' ) ) continue;

		$parsed = aav_lb_tpl_parse( isset( $tpl['html'] ) ? $tpl['html'] : '' );
		if ( ! $parsed ) continue;

		$fields = array();
		foreach ( $parsed as $p ) {
			$fk = 'tplf_' . $slug . '_' . $p['key'];
			if ( 'image' === $p['type'] ) {
				$fields[] = array(
					'key' => $fk, 'label' => $p['label'], 'name' => $p['key'], 'type' => 'image',
					'return_format' => 'url', 'preview_size' => 'medium', 'library' => 'all',
					'instructions' => 'Vide = la valeur par défaut du modèle est utilisée.',
				);
			} else {
				$fields[] = array(
					'key' => $fk, 'label' => $p['label'], 'name' => $p['key'],
					'type' => ( 'textarea' === $p['type'] ? 'textarea' : 'text' ),
					'default_value' => $p['default'],
				);
			}
		}

		acf_add_local_field_group( array(
			'key'      => 'group_aav_tpl_' . $slug,
			'title'    => 'AAV — ' . ( isset( $tpl['name'] ) ? $tpl['name'] : $slug ),
			'fields'   => $fields,
			'location' => array( array( array( 'param' => 'block', 'operator' => '==', 'value' => 'acf/aav-tpl-' . $slug ) ) ),
		) );
	}
}, 20 );

/* ---- Compositions : un modèle = une entrée dans l'inséreur ---- */
add_action( 'init', function () {
	if ( ! function_exists( 'register_block_pattern' ) ) return;
	foreach ( aav_lb_tpl_all() as $slug => $tpl ) {
		$slug = sanitize_key( $slug );
		if ( '' === $slug ) continue;
		$block = 'acf/aav-tpl-' . $slug;
		register_block_pattern( 'aav-lb/tpl-' . $slug, array(
			'title'         => 'AAV — ' . ( isset( $tpl['name'] ) ? $tpl['name'] : $slug ),
			'description'   => isset( $tpl['desc'] ) ? $tpl['desc'] : '',
			'categories'    => array( 'aav-landing' ),
			'keywords'      => array( 'aav', 'modele', $slug ),
			'content'       => '<!-- wp:' . $block . ' {"name":"' . $block . '","mode":"preview"} /-->',
			'viewportWidth' => 1400,
			'inserter'      => true,
		) );
	}
}, 20 );

/* ---- Rendu d'un modèle : remplace les balises par les valeurs ---- */
function aav_lb_render_custom_template( $block, $content = '', $is_preview = false ) {

	if ( $is_preview || is_admin() ) {
		aav_lb_editor_placeholder( 'AAV — Modèle personnalisé' );
		return;
	}
	$name = isset( $block['name'] ) ? $block['name'] : '';
	$slug = str_replace( array( 'acf/aav-tpl-', 'aav-tpl-' ), '', $name );
	$slug = sanitize_key( $slug );

	$tpl = aav_lb_tpl_get( $slug );
	if ( ! $tpl ) { echo '<!-- modèle AAV introuvable -->'; return; }

	$html = isset( $tpl['html'] ) ? $tpl['html'] : '';
	$css  = aav_lb_tpl_clean_css( isset( $tpl['css'] ) ? $tpl['css'] : '' );

	$parsed = aav_lb_tpl_parse( $html );
	foreach ( $parsed as $p ) {
		$val = function_exists( 'get_field' ) ? get_field( $p['key'] ) : '';
		if ( $val === null || $val === false || $val === '' ) $val = $p['default'];

		if ( 'image' === $p['type'] || 'url' === $p['type'] ) {
			$out = esc_url( $val );
		} elseif ( 'textarea' === $p['type'] ) {
			$out = nl2br( esc_html( $val ) );
		} else {
			$out = esc_html( $val );
		}

		$pattern = '/\{\{\s*' . preg_quote( $p['key'], '/' ) . '\s*(?:\|[^}]*)?\}\}/';
		$html = preg_replace( $pattern, str_replace( '$', '\\$', $out ), $html );
	}

	if ( $css ) echo '<style>' . $css . '</style>';
	echo $html;
}

/* ================================================================== *
 * ÉCRAN D'ADMINISTRATION
 * ================================================================== */
add_action( 'admin_menu', function () {
	add_menu_page(
		__( 'AAV — Modèles', 'aav-lb' ),
		__( 'AAV Modèles', 'aav-lb' ),
		'manage_options',
		'aav-lb-templates',
		'aav_lb_admin_page',
		'dashicons-layout',
		58
	);
} );

function aav_lb_admin_notice( $msg, $type = 'success' ) {
	echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
}

function aav_lb_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Accès refusé.', 'aav-lb' ) );

	$action = isset( $_REQUEST['aav_action'] ) ? sanitize_key( $_REQUEST['aav_action'] ) : '';
	$slug   = isset( $_REQUEST['slug'] ) ? sanitize_key( $_REQUEST['slug'] ) : '';
	$notice = '';

	/* ---- Enregistrement ---- */
	if ( 'save' === $action && check_admin_referer( 'aav_lb_save_tpl' ) ) {
		$name = isset( $_POST['tpl_name'] ) ? sanitize_text_field( wp_unslash( $_POST['tpl_name'] ) ) : '';
		$in_slug = isset( $_POST['tpl_slug'] ) ? sanitize_key( wp_unslash( $_POST['tpl_slug'] ) ) : '';
		if ( '' === $in_slug ) $in_slug = sanitize_title( $name );
		$html = isset( $_POST['tpl_html'] ) ? wp_unslash( $_POST['tpl_html'] ) : '';
		$css  = isset( $_POST['tpl_css'] )  ? wp_unslash( $_POST['tpl_css'] )  : '';
		$desc = isset( $_POST['tpl_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['tpl_desc'] ) ) : '';

		if ( '' === $name || '' === $in_slug ) {
			$notice = array( 'Le nom et l\'identifiant sont obligatoires.', 'error' );
		} else {
			aav_lb_tpl_save( $in_slug, array(
				'name'    => $name,
				'desc'    => $desc,
				'css'     => aav_lb_tpl_clean_css( $css ),
				'html'    => $html,
				'updated' => current_time( 'mysql' ),
			) );
			$notice = array( 'Modèle « ' . $name .' » enregistré. Il apparaît dans l\'inséreur (Compositions → AAV).', 'success' );
			$slug = $in_slug;
		}
	}

	/* ---- Suppression ---- */
	if ( 'delete' === $action && $slug && check_admin_referer( 'aav_lb_del_' . $slug ) ) {
		aav_lb_tpl_delete( $slug );
		$notice = array( 'Modèle supprimé.', 'success' );
		$slug = '';
	}

	/* ---- Duplication ---- */
	if ( 'duplicate' === $action && $slug && check_admin_referer( 'aav_lb_dup_' . $slug ) ) {
		$src = aav_lb_tpl_get( $slug );
		if ( $src ) {
			$new = $slug . '-copie';
			$i = 2;
			while ( aav_lb_tpl_get( $new ) ) { $new = $slug . '-copie-' . $i; $i++; }
			$src['name'] = $src['name'] . ' (copie)';
			$src['updated'] = current_time( 'mysql' );
			aav_lb_tpl_save( $new, $src );
			$notice = array( 'Modèle dupliqué.', 'success' );
			$slug = $new;
		}
	}

	/* ---- Surcharge CSS d'un modèle fourni ---- */
	if ( 'save_css' === $action && $slug && check_admin_referer( 'aav_lb_css_' . $slug ) ) {
		$css_in = isset( $_POST['builtin_css'] ) ? wp_unslash( $_POST['builtin_css'] ) : '';
		aav_lb_css_save_override( $slug, $css_in );
		$notice = array( aav_lb_css_is_overridden( $slug ) ? 'CSS personnalisé enregistré.' : 'CSS remis à la version d\'origine.', 'success' );
	}
	if ( 'reset_css' === $action && $slug && check_admin_referer( 'aav_lb_cssreset_' . $slug ) ) {
		aav_lb_css_save_override( $slug, '' );
		$notice = array( 'CSS remis à la version d\'origine.', 'success' );
	}

	/* ---- Surcharge HTML d'un modele fourni ---- */
	if ( 'save_html' === $action && $slug && check_admin_referer( 'aav_lb_html_' . $slug ) ) {
		$html_in = isset( $_POST['builtin_html'] ) ? wp_unslash( $_POST['builtin_html'] ) : '';
		aav_lb_html_save_override( $slug, $html_in );
		$notice = array( aav_lb_html_is_overridden( $slug ) ? 'HTML personnalisé enregistré. Toutes les pages utilisant ce modèle sont à jour.' : 'HTML remis à la version d\'origine (rendu dynamique).', 'success' );
	}
	if ( 'reset_html' === $action && $slug && check_admin_referer( 'aav_lb_htmlreset_' . $slug ) ) {
		aav_lb_html_save_override( $slug, '' );
		$notice = array( 'HTML remis à la version d\'origine (rendu dynamique).', 'success' );
	}

	/* ---- Conversion d'un modèle fourni en modèle éditable ---- */
	if ( 'snapshot' === $action && $slug && check_admin_referer( 'aav_lb_snap_' . $slug ) ) {
		$map = aav_lb_builtin_map();
		if ( isset( $map[ $slug ] ) ) {
			$html = aav_lb_snapshot_html( $slug );
			if ( '' === $html ) {
				$notice = array( 'Impossible de capturer ce modèle.', 'error' );
			} else {
				$new = $slug . '-copie';
				$i = 2;
				while ( aav_lb_tpl_get( $new ) ) { $new = $slug . '-copie-' . $i; $i++; }
				aav_lb_tpl_save( $new, array(
					'name'    => $map[ $slug ]['name'] . ' (copie modifiable)',
					'desc'    => 'Copie de « ' . $map[ $slug ]['name'] . ' », éditable en CSS/HTML.',
					'css'     => aav_lb_css( $slug ),
					'html'    => $html,
					'updated' => current_time( 'mysql' ),
				) );
				$notice = array( 'Modèle copié en version modifiable. Ajoutez des balises {{…}} pour rendre des zones éditables.', 'success' );
				$slug = $new;
				$action = 'edit';
			}
		}
	}

	/* ---- Import JSON ---- */
	if ( 'import' === $action && check_admin_referer( 'aav_lb_import_tpl' ) ) {
		$json = '';
		if ( ! empty( $_FILES['tpl_file']['tmp_name'] ) ) {
			$json = file_get_contents( $_FILES['tpl_file']['tmp_name'] );
		} elseif ( ! empty( $_POST['tpl_json'] ) ) {
			$json = wp_unslash( $_POST['tpl_json'] );
		}
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			$notice = array( 'JSON invalide.', 'error' );
		} else {
			$items = isset( $data['slug'] ) ? array( $data ) : $data;
			$n = 0;
			foreach ( $items as $k => $it ) {
				if ( ! is_array( $it ) ) continue;
				$s = isset( $it['slug'] ) ? sanitize_key( $it['slug'] ) : sanitize_key( (string) $k );
				if ( '' === $s ) continue;
				aav_lb_tpl_save( $s, array(
					'name'    => isset( $it['name'] ) ? sanitize_text_field( $it['name'] ) : $s,
					'desc'    => isset( $it['desc'] ) ? sanitize_text_field( $it['desc'] ) : '',
					'css'     => aav_lb_tpl_clean_css( isset( $it['css'] ) ? $it['css'] : '' ),
					'html'    => isset( $it['html'] ) ? $it['html'] : '',
					'updated' => current_time( 'mysql' ),
				) );
				$n++;
			}
			$notice = array( $n . ' modèle(s) importé(s).', $n ? 'success' : 'error' );
		}
	}

	$all = aav_lb_tpl_all();

	/* ============ ÉCRAN : CSS d'un modèle fourni ============ */
	if ( 'edit_css' === $action && $slug && isset( aav_lb_builtin_map()[ $slug ] ) ) {
		$map = aav_lb_builtin_map();
		$base_u = admin_url( 'admin.php?page=aav-lb-templates' );
		echo '<div class="wrap"><h1>CSS — ' . esc_html( $map[ $slug ]['name'] ) . '</h1>';
		if ( $notice ) aav_lb_admin_notice( $notice[0], $notice[1] );
		if ( aav_lb_css_is_overridden( $slug ) ) {
			echo '<div class="notice notice-info"><p>Ce modèle utilise actuellement un <strong>CSS personnalisé</strong>. Videz le champ et enregistrez pour revenir à la version d\'origine.</p></div>';
		}
		echo '<form method="post" action="' . esc_url( $base_u ) . '">';
		wp_nonce_field( 'aav_lb_css_' . $slug );
		echo '<input type="hidden" name="aav_action" value="save_css"><input type="hidden" name="slug" value="' . esc_attr( $slug ) . '">';
		echo '<textarea name="builtin_css" rows="28" class="large-text code" spellcheck="false">' . esc_textarea( aav_lb_css( $slug ) ) . '</textarea>';
		echo '<p class="description">Modifie l\'apparence du modèle sans réinstaller l\'extension. La version d\'origine reste dans les fichiers et peut être restaurée à tout moment.</p>';
		submit_button( 'Enregistrer le CSS' );
		echo '<a href="' . esc_url( $base_u ) . '" class="button">Retour</a>';
		echo '</form></div>';
		return;
	}

	/* ============ ÉCRAN : HTML d'un modèle fourni ============ */
	if ( 'edit_html' === $action && $slug && isset( aav_lb_builtin_map()[ $slug ] ) ) {
		$map = aav_lb_builtin_map();
		$base_u = admin_url( 'admin.php?page=aav-lb-templates' );
		$src_note = '';
		if ( aav_lb_html_is_overridden( $slug ) ) {
			$cur = aav_lb_html_override( $slug );
		} else {
			list( $cur, $src_pid ) = aav_lb_snapshot_from_page( $slug );
			if ( '' !== $cur && $src_pid ) {
				$src_note = 'Pré-rempli depuis la page « ' . get_the_title( $src_pid ) . ' » : les textes affichés sont ceux du site, y compris les modifications faites via les champs SCF.';
			} else {
				$cur = aav_lb_snapshot_html( $slug );
				$src_note = 'Aucune page publiée n\'utilise ce modèle : pré-rempli avec les valeurs par défaut.';
			}
		}
		echo '<div class="wrap"><h1>HTML — ' . esc_html( $map[ $slug ]['name'] ) . '</h1>';
		if ( $notice ) aav_lb_admin_notice( $notice[0], $notice[1] );
		if ( aav_lb_html_is_overridden( $slug ) ) {
			echo '<div class="notice notice-info"><p>Ce modèle utilise actuellement un <strong>HTML personnalisé</strong> : toutes les pages qui l\'utilisent affichent ce contenu. Videz le champ et enregistrez pour revenir au rendu d\'origine.</p></div>';
		} else {
			echo '<div class="notice notice-warning"><p><strong>À savoir avant d\'enregistrer :</strong> tant qu\'un HTML personnalisé est actif, le modèle est rendu tel quel sur toutes les pages qui l\'utilisent, et les champs SCF de ces pages ne sont plus lus (contenu identique partout). Le script embarqué du modèle est inclus et éditable ci-dessous. Restaurer l\'original rétablit le rendu dynamique par champs.</p></div>';
		}
		echo '<form method="post" action="' . esc_url( $base_u ) . '">';
		wp_nonce_field( 'aav_lb_html_' . $slug );
		echo '<input type="hidden" name="aav_action" value="save_html"><input type="hidden" name="slug" value="' . esc_attr( $slug ) . '">';
		echo '<textarea name="builtin_html" rows="30" class="large-text code" spellcheck="false">' . esc_textarea( $cur ) . '</textarea>';
		if ( $src_note ) echo '<p class="description" style="font-weight:600">' . esc_html( $src_note ) . '</p>';
		echo '<p class="description">Le script embarqué du modèle est inclus ci-dessus. La structure d\'origine reste codée dans l\'extension et peut être restaurée à tout moment.</p>';
		submit_button( 'Enregistrer le HTML' );
		echo '<a href="' . esc_url( $base_u ) . '" class="button">Retour</a>';
		echo '</form></div>';
		return;
	}

	$edit = ( 'edit' === $action || ( 'save' === $action && $slug ) ) && isset( $all[ $slug ] ) ? $all[ $slug ] : null;
	$is_new = ( 'new' === $action );

	echo '<div class="wrap"><h1>AAV — Studio de modèles</h1>';
	if ( $notice ) aav_lb_admin_notice( $notice[0], $notice[1] );

	$base = admin_url( 'admin.php?page=aav-lb-templates' );

	/* ============ FORMULAIRE ============ */
	if ( $edit || $is_new ) {
		$v = function ( $k, $d = '' ) use ( $edit ) { return $edit && isset( $edit[ $k ] ) ? $edit[ $k ] : $d; };
		echo '<h2>' . ( $is_new ? 'Nouveau modèle' : 'Modifier « ' . esc_html( $v( 'name' ) ) . ' »' ) . '</h2>';
		echo '<form method="post" action="' . esc_url( $base ) . '">';
		wp_nonce_field( 'aav_lb_save_tpl' );
		echo '<input type="hidden" name="aav_action" value="save">';
		echo '<table class="form-table"><tbody>';
		echo '<tr><th><label for="tpl_name">Nom du modèle</label></th><td><input name="tpl_name" id="tpl_name" type="text" class="regular-text" required value="' . esc_attr( $v( 'name' ) ) . '"></td></tr>';
		echo '<tr><th><label for="tpl_slug">Identifiant</label></th><td><input name="tpl_slug" id="tpl_slug" type="text" class="regular-text" value="' . esc_attr( $is_new ? '' : $slug ) . '"' . ( $is_new ? '' : ' readonly' ) . '><p class="description">Lettres minuscules et tirets. Laisser vide pour le déduire du nom.</p></td></tr>';
		echo '<tr><th><label for="tpl_desc">Description</label></th><td><input name="tpl_desc" id="tpl_desc" type="text" class="large-text" value="' . esc_attr( $v( 'desc' ) ) . '"></td></tr>';
		echo '<tr><th><label for="tpl_css">CSS</label></th><td><textarea name="tpl_css" id="tpl_css" rows="12" class="large-text code" spellcheck="false">' . esc_textarea( $v( 'css' ) ) . '</textarea></td></tr>';
		echo '<tr><th><label for="tpl_html">HTML</label></th><td><textarea name="tpl_html" id="tpl_html" rows="20" class="large-text code" spellcheck="false">' . esc_textarea( $v( 'html' ) ) . '</textarea>';
		echo '<p class="description"><strong>Zones éditables :</strong> écrire <code>{{cle|Libellé|type|valeur par défaut}}</code> dans le HTML. Types : <code>text</code>, <code>textarea</code>, <code>image</code>, <code>url</code>.<br>Exemple : <code>&lt;h1&gt;{{titre|Titre principal|text|Bonjour}}&lt;/h1&gt;</code> — un champ « Titre principal » apparaîtra dans l\'éditeur.</p></td></tr>';
		echo '</tbody></table>';
		submit_button( 'Enregistrer le modèle' );
		echo '<a href="' . esc_url( $base ) . '" class="button">Annuler</a>';
		echo '</form>';

		if ( $edit ) {
			$p = aav_lb_tpl_parse( $v( 'html' ) );
			echo '<h3>Champs détectés (' . count( $p ) . ')</h3>';
			if ( $p ) {
				echo '<table class="widefat striped" style="max-width:760px"><thead><tr><th>Clé</th><th>Libellé</th><th>Type</th><th>Défaut</th></tr></thead><tbody>';
				foreach ( $p as $x ) {
					$dflt = (string) $x['default'];
					$dflt = function_exists( 'mb_substr' ) ? mb_substr( $dflt, 0, 60 ) : substr( $dflt, 0, 60 );
					echo '<tr><td><code>' . esc_html( $x['key'] ) . '</code></td><td>' . esc_html( $x['label'] ) . '</td><td>' . esc_html( $x['type'] ) . '</td><td>' . esc_html( $dflt ) . '</td></tr>';
				}
				echo '</tbody></table>';
			} else {
				echo '<p><em>Aucune balise détectée : le modèle sera inséré tel quel, sans champs éditables.</em></p>';
			}
			$export = wp_json_encode( array_merge( array( 'slug' => $slug ), $edit ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
			echo '<h3>Export JSON</h3><textarea rows="6" class="large-text code" readonly onclick="this.select()">' . esc_textarea( $export ) . '</textarea>';
		}
		echo '</div>';
		return;
	}

	/* ============ LISTE ============ */
	echo '<p><a href="' . esc_url( add_query_arg( 'aav_action', 'new', $base ) ) . '" class="button button-primary">Nouveau modèle</a></p>';

	if ( ! $all ) {
		echo '<p>Aucun modèle personnalisé pour l\'instant. Les modèles livrés avec l\'extension (Alsace, Paris, Alpes, Sustainability, Our Story) restent disponibles dans l\'inséreur.</p>';
	} else {
		echo '<table class="widefat striped"><thead><tr><th>Nom</th><th>Identifiant</th><th>Champs</th><th>Modifié</th><th>Actions</th></tr></thead><tbody>';
		foreach ( $all as $s => $t ) {
			$nb = count( aav_lb_tpl_parse( isset( $t['html'] ) ? $t['html'] : '' ) );
			$edit_u = add_query_arg( array( 'aav_action' => 'edit', 'slug' => $s ), $base );
			$dup_u  = wp_nonce_url( add_query_arg( array( 'aav_action' => 'duplicate', 'slug' => $s ), $base ), 'aav_lb_dup_' . $s );
			$del_u  = wp_nonce_url( add_query_arg( array( 'aav_action' => 'delete', 'slug' => $s ), $base ), 'aav_lb_del_' . $s );
			echo '<tr>';
			echo '<td><strong>' . esc_html( isset( $t['name'] ) ? $t['name'] : $s ) . '</strong></td>';
			echo '<td><code>' . esc_html( $s ) . '</code></td>';
			echo '<td>' . (int) $nb . '</td>';
			echo '<td>' . esc_html( isset( $t['updated'] ) ? $t['updated'] : '—' ) . '</td>';
			echo '<td><a href="' . esc_url( $edit_u ) . '">Modifier</a> | <a href="' . esc_url( $dup_u ) . '">Dupliquer</a> | <a href="' . esc_url( $del_u ) . '" onclick="return confirm(\'Supprimer ce modèle ?\')" style="color:#b32d2e">Supprimer</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	/* ---- Modèles fournis avec l'extension ---- */
	echo '<h2 style="margin-top:2.4em">Modèles fournis avec l\'extension</h2>';
	echo '<p class="description">Leur structure est codée dans l\'extension. Vous pouvez modifier directement leur CSS et leur HTML (les pages qui les utilisent sont mises à jour automatiquement), ou en faire une copie indépendante.</p>';
	echo '<table class="widefat striped"><thead><tr><th>Nom</th><th>Identifiant</th><th>CSS</th><th>HTML</th><th>Actions</th></tr></thead><tbody>';
	foreach ( aav_lb_builtin_map() as $bs => $bt ) {
		$css_u  = add_query_arg( array( 'aav_action' => 'edit_css',  'slug' => $bs ), $base );
		$html_u = add_query_arg( array( 'aav_action' => 'edit_html', 'slug' => $bs ), $base );
		$snap_u = wp_nonce_url( add_query_arg( array( 'aav_action' => 'snapshot', 'slug' => $bs ), $base ), 'aav_lb_snap_' . $bs );
		$rst_u  = wp_nonce_url( add_query_arg( array( 'aav_action' => 'reset_css',  'slug' => $bs ), $base ), 'aav_lb_cssreset_' . $bs );
		$rsh_u  = wp_nonce_url( add_query_arg( array( 'aav_action' => 'reset_html', 'slug' => $bs ), $base ), 'aav_lb_htmlreset_' . $bs );
		$ovr    = aav_lb_css_is_overridden( $bs );
		$ovh    = aav_lb_html_is_overridden( $bs );
		echo '<tr>';
		echo '<td><strong>' . esc_html( $bt['name'] ) . '</strong></td>';
		echo '<td><code>' . esc_html( $bs ) . '</code></td>';
		echo '<td>' . ( $ovr ? '<span style="color:#b26b00">personnalisé</span>' : '<span style="color:#666">d\'origine</span>' ) . '</td>';
		echo '<td>' . ( $ovh ? '<span style="color:#b26b00">personnalisé</span>' : '<span style="color:#666">d\'origine</span>' ) . '</td>';
		echo '<td><a href="' . esc_url( $html_u ) . '">Modifier le HTML</a> | <a href="' . esc_url( $css_u ) . '">Modifier le CSS</a> | <a href="' . esc_url( $snap_u ) . '">Copier en modèle modifiable</a>';
		if ( $ovr ) echo ' | <a href="' . esc_url( $rst_u ) . '" onclick="return confirm(\'Restaurer le CSS d\\\'origine ?\')">Restaurer CSS</a>';
		if ( $ovh ) echo ' | <a href="' . esc_url( $rsh_u ) . '" onclick="return confirm(\'Restaurer le HTML d\\\'origine ?\')">Restaurer HTML</a>';
		echo '</td></tr>';
	}
	echo '</tbody></table>';

	/* ---- Import ---- */
	echo '<h2 style="margin-top:2em">Importer un modèle (JSON)</h2>';
	echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( $base ) . '">';
	wp_nonce_field( 'aav_lb_import_tpl' );
	echo '<input type="hidden" name="aav_action" value="import">';
	echo '<p><input type="file" name="tpl_file" accept=".json,application/json"></p>';
	echo '<p>ou coller le JSON :</p>';
	echo '<textarea name="tpl_json" rows="6" class="large-text code" spellcheck="false" placeholder=\'{"slug":"ma-page","name":"Ma page","css":"…","html":"…"}\'></textarea>';
	submit_button( 'Importer' );
	echo '</form>';

	echo '</div>';
}

/* ================================================================== *
 * ANCRES + SMOOTH SCROLL : intercepte les clics d'ancre de la meme page
 * et delegue le defilement a Locomotive (le scroll natif est neutralise
 * par le smooth scroll du theme). Repli natif si l'instance est absente.
 * ================================================================== */
add_action( 'wp_footer', function () {
	?>
<script>/*aav-lb-anchors*/
document.addEventListener('click', function (e) {
	var a = e.target.closest ? e.target.closest('a[href*="#"]') : null;
	if (!a) return;
	var url;
	try { url = new URL(a.href, location.href); } catch (err) { return; }
	if (url.host !== location.host || url.pathname !== location.pathname) return;
	if (!url.hash || url.hash === '#') return;
	var el = document.querySelector(url.hash);
	if (!el) return;
	e.preventDefault();
	var l = window.__aavLoco;
	if (l && typeof l.scrollTo === 'function') {
		try { l.scrollTo(el, { offset: -80 }); return; } catch (err) {}
	}
	el.scrollIntoView({ behavior: 'smooth' });
});
</script>
	<?php
}, 98 );

/* ================================================================== *
 * MODÈLES FOURNIS : visibilité, surcharge CSS, conversion en éditable
 * ================================================================== */

define( 'AAV_LB_CSS_OPTION', 'aav_lb_css_overrides' );
define( 'AAV_LB_HTML_OPTION', 'aav_lb_html_overrides' );

/* ---- Surcharge HTML d'un modele fourni (miroir de la surcharge CSS) ---- */
function aav_lb_html_override( $slug ) {
	$ov = get_option( AAV_LB_HTML_OPTION, array() );
	return ( is_array( $ov ) && ! empty( $ov[ $slug ] ) ) ? $ov[ $slug ] : '';
}
function aav_lb_html_is_overridden( $slug ) {
	$ov = get_option( AAV_LB_HTML_OPTION, array() );
	return is_array( $ov ) && ! empty( $ov[ $slug ] );
}
function aav_lb_html_save_override( $slug, $html ) {
	$ov = get_option( AAV_LB_HTML_OPTION, array() );
	if ( ! is_array( $ov ) ) $ov = array();
	if ( '' === trim( (string) $html ) ) unset( $ov[ $slug ] );
	else $ov[ $slug ] = $html;
	update_option( AAV_LB_HTML_OPTION, $ov, false );
}
/* Rend la surcharge si elle existe (CSS effectif inclus). Retourne true si rendue. */
function aav_lb_maybe_render_override( $slug ) {
	if ( ! aav_lb_html_is_overridden( $slug ) ) return false;
	$css = aav_lb_css( $slug );
	if ( $css ) echo '<style>' . $css . '</style>';
	echo aav_lb_html_override( $slug );
	return true;
}

/* Liste des modèles livrés avec l'extension (slug => infos) */
/* URL d'un bouton : une ancre (#plan) est resolue sur la page courante
   (la balise <base> du theme casse les ancres relatives), une URL
   complete ou un chemin sont utilises tels quels. */
function aav_lb_cta_url( $v, $fallback = '#plan' ) {
	$v = trim( (string) $v );
	if ( '' === $v ) $v = $fallback;
	if ( 0 === strpos( $v, '#' ) ) return esc_url( get_permalink() . $v );
	return esc_url( $v );
}

function aav_lb_builtin_map() {
	return array(
		'alsace'         => array( 'name' => 'Christmas in Alsace',      'block' => 'acf/aav-alsace',         'css' => 'alsace.css',         'render' => 'aav_lb_render_alsace' ),
		'paris'          => array( 'name' => 'Christmas in Paris',       'block' => 'acf/aav-paris',          'css' => 'paris.css',          'render' => 'aav_lb_render_paris' ),
		'alps'           => array( 'name' => 'Winter in the French Alps','block' => 'acf/aav-alps',           'css' => 'alps.css',           'render' => 'aav_lb_render_alps' ),
		'sustainability' => array( 'name' => 'Sustainability',           'block' => 'acf/aav-sustainability', 'css' => 'sustainability.css', 'render' => 'aav_lb_render_sustainability' ),
		'little-black-book' => array( 'name' => 'The Little Black Book',  'block' => 'acf/aav-little-black-book', 'css' => 'little-black-book.css', 'render' => 'aav_lb_render_little_black_book' ),
		'our-story'      => array( 'name' => 'Our Story (20 ans)',       'block' => 'acf/aav-our-story',      'css' => 'our-story.css',      'render' => 'aav_lb_render_our_story' ),
		'lb'             => array( 'name' => 'Landing sur-mesure (sections)', 'block' => 'acf/aav-builder',   'css' => 'lb.css',             'render' => 'aav_lb_render_builder' ),
	);
}

/* CSS effectif d'un modèle fourni : surcharge en base si présente, sinon fichier */
function aav_lb_css( $slug ) {
	$ov = get_option( AAV_LB_CSS_OPTION, array() );
	if ( is_array( $ov ) && ! empty( $ov[ $slug ] ) ) {
		return aav_lb_tpl_clean_css( $ov[ $slug ] );
	}
	$map = aav_lb_builtin_map();
	$file = isset( $map[ $slug ]['css'] ) ? $map[ $slug ]['css'] : $slug . '.css';
	$c = @file_get_contents( AAV_LB_DIR . 'assets/' . $file );
	return $c ? $c : '';
}

function aav_lb_css_is_overridden( $slug ) {
	$ov = get_option( AAV_LB_CSS_OPTION, array() );
	return is_array( $ov ) && ! empty( $ov[ $slug ] );
}
function aav_lb_css_original( $slug ) {
	$map = aav_lb_builtin_map();
	$file = isset( $map[ $slug ]['css'] ) ? $map[ $slug ]['css'] : $slug . '.css';
	$c = @file_get_contents( AAV_LB_DIR . 'assets/' . $file );
	return $c ? $c : '';
}
function aav_lb_css_save_override( $slug, $css ) {
	$ov = get_option( AAV_LB_CSS_OPTION, array() );
	if ( ! is_array( $ov ) ) $ov = array();
	if ( '' === trim( (string) $css ) ) unset( $ov[ $slug ] );
	else $ov[ $slug ] = aav_lb_tpl_clean_css( $css );
	update_option( AAV_LB_CSS_OPTION, $ov, false );
}

/* Capture le HTML d'un modele depuis une page publiee qui l'utilise
   (valeurs reelles de la page, y compris les textes modifies via SCF).
   Retourne array( html, post_id ) ; html vide si aucune page trouvee. */
function aav_lb_snapshot_from_page( $slug ) {
	global $wpdb;
	$map = aav_lb_builtin_map();
	if ( ! isset( $map[ $slug ] ) ) return array( '', 0 );
	$block_name = $map[ $slug ]['block'];

	$pid = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts}
		 WHERE post_status = 'publish' AND post_type IN ('page','post')
		 AND post_content LIKE %s ORDER BY ID ASC LIMIT 1",
		'%' . $wpdb->esc_like( 'wp:' . $block_name ) . '%'
	) );
	if ( ! $pid ) return array( '', 0 );

	$post = get_post( $pid );
	if ( ! $post ) return array( '', 0 );

	$find = function ( $blocks ) use ( &$find, $block_name ) {
		foreach ( $blocks as $b ) {
			if ( isset( $b['blockName'] ) && $b['blockName'] === $block_name ) return $b;
			if ( ! empty( $b['innerBlocks'] ) ) {
				$r = $find( $b['innerBlocks'] );
				if ( $r ) return $r;
			}
		}
		return null;
	};
	$block = $find( parse_blocks( $post->post_content ) );
	if ( ! $block ) return array( '', 0 );

	global $post_backup;
	$GLOBALS['post'] = $post;
	setup_postdata( $post );
	/* Rendu force dans la langue du site (le front), pas celle du profil
	   admin : evite de figer des chaines traduites dans les captures. */
	$site_locale = get_option( 'WPLANG' ) ? get_option( 'WPLANG' ) : 'en_US';
	$switched    = switch_to_locale( $site_locale );
	$html = '';
	try { $html = render_block( $block ); }
	catch ( Throwable $e ) { $html = ''; }
	if ( $switched ) {
		restore_current_locale();
	}
	wp_reset_postdata();

	$html = preg_replace( '#<style\b[^>]*>.*?</style>#is', '', (string) $html );
	return array( trim( $html ), $pid );
}

/* Capture le HTML rendu d'un modèle fourni (valeurs par défaut) */
function aav_lb_snapshot_html( $slug ) {
	$map = aav_lb_builtin_map();
	if ( ! isset( $map[ $slug ] ) ) return '';
	$fn = $map[ $slug ]['render'];
	if ( ! function_exists( $fn ) ) return '';

	/* Hors contexte de bloc, get_field() ne renvoie pas les valeurs par
	   défaut : on les force le temps de la capture. */
	$defaults_filter = function ( $null, $post_id, $field ) {
		if ( isset( $field['default_value'] ) && '' !== $field['default_value'] ) {
			return $field['default_value'];
		}
		return $null;
	};
	add_filter( 'acf/pre_load_value', $defaults_filter, 99, 3 );

	ob_start();
	try { call_user_func( $fn, array( 'name' => $map[ $slug ]['block'] ) ); }
	catch ( Throwable $e ) { ob_end_clean(); remove_filter( 'acf/pre_load_value', $defaults_filter, 99 ); return ''; }
	$html = ob_get_clean();

	remove_filter( 'acf/pre_load_value', $defaults_filter, 99 );
	/* retirer la balise <style> : le CSS est géré séparément */
	$html = preg_replace( '#<style\b[^>]*>.*?</style>#is', '', $html );
	return trim( (string) $html );
}

/* ================================================================== *
 * MISES À JOUR DEPUIS GITHUB (mécanisme natif de WordPress)
 * -----------------------------------------------------------------
 * L'en-tête « Update URI » délègue à ce filtre. Il interroge la
 * dernière release publique du dépôt, compare le tag (vX.Y.Z) à la
 * version installée et propose le zip attaché à la release.
 * Réponse mise en cache 1 h (15 min en cas d'échec réseau), purgée à la
 * visite des pages Mises à jour / Extensions et par le toolkit.
 * ================================================================== */
define( 'AAV_LB_FILE', plugin_basename( __FILE__ ) );
define( 'AAV_LB_REPO', 'biolay-group/aav-landing-blocks' );

add_filter( 'update_plugins_github.com', function ( $update, $plugin_data, $plugin_file ) {
	if ( AAV_LB_FILE !== $plugin_file ) return $update;

	$release = get_transient( 'aav_lb_gh_release' );
	if ( false === $release ) {
		$res = wp_remote_get( 'https://api.github.com/repos/' . AAV_LB_REPO . '/releases/latest', array(
			'timeout' => 10,
			'headers' => array( 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'AAV-Landing-Blocks/' . $plugin_data['Version'] ),
		) );
		if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
			set_transient( 'aav_lb_gh_release', array(), 15 * MINUTE_IN_SECONDS );
			return $update;
		}
		$release = json_decode( wp_remote_retrieve_body( $res ), true );
		set_transient( 'aav_lb_gh_release', $release, HOUR_IN_SECONDS );
	}
	if ( empty( $release['tag_name'] ) ) return $update;

	$remote = ltrim( $release['tag_name'], 'vV' );
	if ( ! preg_match( '/^\d+(\.\d+)*$/', $remote ) ) return $update;
	if ( version_compare( $remote, $plugin_data['Version'], '<=' ) ) return $update;

	$package = '';
	foreach ( (array) ( isset( $release['assets'] ) ? $release['assets'] : array() ) as $asset ) {
		$url = isset( $asset['browser_download_url'] ) ? $asset['browser_download_url'] : '';
		if ( $url && preg_match( '#^https://github\.com/#', $url ) && preg_match( '/\.zip$/i', $url ) ) { $package = $url; break; }
	}
	if ( '' === $package && ! empty( $release['zipball_url'] ) ) $package = $release['zipball_url'];
	if ( '' === $package ) return $update;

	return array(
		'slug'    => dirname( AAV_LB_FILE ),
		'version' => $remote,
		'url'     => 'https://github.com/' . AAV_LB_REPO,
		'package' => $package,
	);
}, 10, 3 );

add_action( 'upgrader_process_complete', function () { delete_transient( 'aav_lb_gh_release' ); } );

/* Visiter Tableau de bord > Mises à jour ou Extensions réinterroge GitHub
   (WordPress limite lui-même la fréquence de ces vérifications). */
foreach ( array( 'load-update-core.php', 'load-plugins.php' ) as $aav_lb_hook ) {
	add_action( $aav_lb_hook, function () { delete_transient( 'aav_lb_gh_release' ); }, 1 );
}
