# WPSoluces Core (Plugin)
## Installation


1. **Copiez** le dossier `wpsoluces-core/` dans :

```

wp-content/plugins/

```
2. Activez « WPSoluces Core » depuis l'administration WordPress.
3. Ce plugin nécessite **PHP 8.2** ou supérieur.

---


## Modules inclus

Chaque module est démarré automatiquement par `Core\Init::run()` :

| Module                       | Fonctionnalité principale                                                                                         |
|------------------------------|-------------------------------------------------------------------------------------------------------------------|
| **TagManager**               | Intègre et déclenche Google Tag Manager sur chaque page.   -> menu réglages de wordpress                                                      |
| **LoginRedirect**            | Redirige tout accès à `/wp-admin` ou `/wp-login.php` vers `/connect`.   -> menu réglages de wordpress                                            |
| **DuplicatePost**            | Active la duplication d’articles et de pages directement depuis le tableau de bord par défaut.                              |
| **LoginLimiter**             | Verrouille les tentatives de connexion (5 essais max sur 30 min), même pour des comptes inexistants, cela évite le flood.             |
| **HideVersion**              | Cache la version de WordPress (meta generator, paramètres `?ver` sur les assets), pour réduire la surface d’attaque. |
| **DisableXmlRpc**            | Désactive complètement XML-RPC, les pingbacks/RSD/WLW, et bloque toute requête directe vers `xmlrpc.php`.         |

---
## Support et contributions

Pour toute suggestion ou bug, ouvrez une issue sur le dépôt Git ou contactez Guillaume de WPSoluces.

https://wpsoluces.com/
