# Eagle Forms

Eagle Forms est un plugin WordPress léger inspiré de WPForms. Il permet de créer des formulaires personnalisés à l'aide d'une interface simple dans l'administration et d'afficher ces formulaires grâce à un shortcode.

## Fonctionnalités principales

- Constructeur de champs glisser-déposer pour les types de champs texte, email, zone de texte, case à cocher, nombre et liste déroulante.
- Génération automatique d'un shortcode `[eagle_form id="123"]` pour chaque formulaire.
- Gestion des soumissions : les entrées sont enregistrées en tant que type de contenu personnalisé et consultables dans le tableau de bord.
- Notification par e-mail envoyée à l'administrateur lors d'une nouvelle soumission.
- Styles front-end inclus pour un rendu cohérent des formulaires et des messages de confirmation.

## Installation

1. Copiez le dossier `eagle-forms` dans `wp-content/plugins/`.
2. Activez **Eagle Forms** depuis le menu Extensions de votre site WordPress.
3. Créez un nouveau formulaire via **Eagle Forms → Add New**.
4. Ajoutez le shortcode généré dans une page ou un article.

## Développement

Le code est structuré autour de classes situées dans le dossier `includes` et de ressources (JS/CSS) dans le dossier `assets`.

- `class-eagle-forms.php` : point d'entrée du plugin et enregistrement des types de contenus.
- `class-eagle-forms-admin.php` : logique du back-office (menus, metaboxes, sauvegarde).
- `class-eagle-forms-frontend.php` : rendu des formulaires, validation et traitement des soumissions.

## Support

Ce projet est fourni à des fins d'exemple. Pour une utilisation en production, pensez à ajouter une gestion des erreurs plus avancée, la possibilité de personnaliser les notifications et une protection anti-spam (reCAPTCHA, honeypot, etc.).
