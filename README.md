# WPSoluces Core (MU-Plugin)

Un **Must-Use Plugin** WordPress, placé dans le dossier `wp-content/mu-plugins/wpsoluces-core/`, qui regroupe plusieurs modules prêts à l’emploi pour renforcer et personnaliser votre site.

---

## Installation

Un **Must-Use Plugin** WordPress, placé dans le dossier `wp-content/mu-plugins/wpsoluces-core/`, qui regroupe plusieurs modules prêts à l’emploi pour renforcer et personnaliser votre site.

---

## Installation

1. **Copiez** le dossier `wpsoluces-core/` entier dans :  
```

wp-content/mu-plugins/

```
2. Vérifiez que le fichier principal `mu-plugins/wpsoluces-core.php` est bien chargé automatiquement par WordPress (les MU-Plugins sont activés par défaut).

---


## Modules inclus

Chaque module est démarré automatiquement par `Core\Init::run()` :

| Module                       | Fonctionnalité principale                                                                                         |
|------------------------------|-------------------------------------------------------------------------------------------------------------------|
| **TagManager**               | Intègre et déclenche Google Tag Manager sur chaque page.   -> menu réglages de wordpress                                                      |
| **LoginRedirect**            | Redirige tout accès à `/wp-admin` ou `/wp-login.php` vers `/connect`.   -> menu réglages de wordpress                                            |
| **DuplicatePost**            | Active la duplication d’articles et de pages directement depuis l’admin, par défaut.                              |
| **LoginLimiter**             | Verrouille les tentatives de connexion (5 essais max sur 30 min), même pour des comptes inexistants, cela évite le flood.             |
| **HideVersion**              | Cache la version de WordPress (meta generator, paramètres `?ver` sur les assets), pour réduire la surface d’attaque. |
| **DisableXmlRpc**            | Désactive complètement XML-RPC, les pingbacks/RSD/WLW, et bloque toute requête directe vers `xmlrpc.php`.         |

---

## Pourquoi MU-Plugin ?

- **Chargement automatique** : pas besoin d’activer manuellement.
- **Sécurité renforcée** : modules critiques toujours en place.
- **Centralisation** : tous vos réglages “core” dans un seul dossier.

---

## Support et contributions

Pour toute suggestion ou bug, ouvrez une issue sur le dépôt Git ou contactez Guillaume de WPSoluces.
