Using the User Bundle
=====================

The user bundle should be easy to use.

Creating and managing user accounts
-----------------------------------

Create a new user at /admin/user/new or

```bash
./bin/console fos:user:create
```

Promote the user to admin by navigating to /admin/user -> user profile -> edit, or

```bash
./bin/console fos:user:promote
```

Reset a user's password by navigating to /admin/user -> password or

```bash
./bin/console fos:user:change-password
```

Activate or deactivate a user account by navigating to /admin/user -> user profile -> edit, or

```bash
./bin/console fos:user:activate
./bin/console fos:user:deactivate
```

Logging In and Out
------------------

The User Menu (described below) will generate a Login link for unauthenticated users. The
login form contains a link to reset the users password.

For authenticated users, the menu will contain links to their profile, change password, 
and logout functions. Users with ROLE_ADMIN privileges will also have a Users menu item.

Menus
-----

```twig
    {{ knp_menu_render('user') }}
```
