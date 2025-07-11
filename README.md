# WP Starter Pack (Plugin)
## Installation



1. **Téléversez wpstarterpack.zip** depuis le backoffice WordPress ou **Copiez** le dossier `wpstarterpack/` dans :
```

wp-content/plugins/

```
2. Activez « WP Starter Pack » depuis l'administration WordPress.
3. Ce plugin nécessite **PHP 8.2** ou supérieur.

---


## Modules inclus

Chaque module est démarré automatiquement par `Core\Init::run()` :

| Module                       | Fonctionnalité principale                                                                                         |
|------------------------------|-------------------------------------------------------------------------------------------------------------------|
| **TagManager**               | Intègre Google Tag Manager sur chaque page, avec filtrage possible des réglages.   -> menu Réglages |
| **LoginRedirect**            | Redirige tout accès à `/wp-admin` ou `/wp-login.php` vers une URL de connexion personnalisable.   -> menu Réglages |
| **DuplicatePost**            | Active la duplication d’articles et de pages directement depuis le tableau de bord par défaut.                              |
| **LoginLimiter**             | Verrouille les tentatives de connexion après 5 échecs pendant 30&nbsp;minutes, même pour des comptes inexistants. |
| **HideVersion**              | Cache la version de WordPress (meta generator, paramètres `?ver` sur les assets), pour réduire la surface d’attaque. |
| **DisableXmlRpc**            | Désactive complètement XML-RPC, les pingbacks/RSD/WLW, et bloque toute requête directe vers `xmlrpc.php`.         |

---
## Support et contributions

Pour toute suggestion ou bug, ouvrez une issue sur le dépôt Git ou contactez Guillaume de WPSoluces.

https://wpsoluces.com/
