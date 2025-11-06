<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client jQuery</title>
    <link rel="stylesheet" href="/assets/css/pico.min.css">
    <script src="/assets/js/jquery-3.7.1.min.js"></script>
</head>
<body>
<main class="container">
    <h1>Client jQuery</h1>

    <section id="authentication">
        <form id="auth-form">
            <fieldset>
                <label for="auth-email">Adresse courriel</label>
                <input type="email" id="auth-email" name="email" required>
            </fieldset>
            <fieldset>
                <label for="auth-password">Mot de passe</label>
                <input type="password" id="auth-password" name="password" required>
            </fieldset>
            <button type="submit">Se connecter</button>
        </form>

        <button id="logout-button" type="button" style="display: none;">Se déconnecter</button>

        <p id="auth-message"></p>
    </section>

    <section id="registration">
        <h2>Créer un compte</h2>
        <form id="register-form">
            <fieldset>
                <label for="register-first-name">Prénom</label>
                <input type="text" id="register-first-name" name="first_name" required>
            </fieldset>
            <fieldset>
                <label for="register-last-name">Nom</label>
                <input type="text" id="register-last-name" name="last_name" required>
            </fieldset>
            <fieldset>
                <label for="register-pseudo">Pseudo</label>
                <input type="text" id="register-pseudo" name="pseudo" required>
            </fieldset>
            <fieldset>
                <label for="register-birth-date">Date de naissance</label>
                <input type="date" id="register-birth-date" name="birth_date" required>
            </fieldset>
            <fieldset>
                <label for="register-gender">Genre</label>
                <select id="register-gender" name="gender" required>
                    <option value="" selected disabled>-- Sélectionnez --</option>
                    <option value="m">Masculin</option>
                    <option value="f">Féminin</option>
                    <option value="o">Autre</option>
                </select>
            </fieldset>
            <fieldset>
                <label for="register-avatar">Avatar</label>
                <input type="text" id="register-avatar" name="avatar" required>
            </fieldset>
            <fieldset>
                <label for="register-email">Adresse courriel</label>
                <input type="email" id="register-email" name="email" required>
            </fieldset>
            <fieldset>
                <label for="register-password">Mot de passe</label>
                <input type="password" id="register-password" name="password" required>
            </fieldset>
            <button type="submit">Créer le compte</button>
        </form>
        <p id="register-message"></p>
    </section>

    <section id="profile-section" style="display: none;">
        <h2>Modifier mon compte</h2>
        <form id="profile-form">
            <fieldset>
                <label for="profile-pseudo">Pseudo</label>
                <input type="text" id="profile-pseudo" name="pseudo">
            </fieldset>
            <fieldset>
                <label for="profile-gender">Genre</label>
                <select id="profile-gender" name="gender">
                    <option value="" selected disabled>-- Sélectionnez --</option>
                    <option value="m">Masculin</option>
                    <option value="f">Féminin</option>
                    <option value="o">Autre</option>
                </select>
            </fieldset>
            <fieldset>
                <label for="profile-avatar">Avatar</label>
                <input type="text" id="profile-avatar" name="avatar">
            </fieldset>
            <button type="submit">Mettre à jour</button>
        </form>
        <p id="profile-message"></p>
    </section>
</main>
<script>
    let dataSample = {
        first_name: "Adrien",
        last_name: "Girard",
        pseudo: "CodeRanger",
        birth_date: "2004-12-03",
        gender: "m",
        avatar: "coderanger.jpg",
        email: "adrien.girard@example.com",
        password: "password"
    };

    let partialDataSample = {
        pseudo: "Avalonee",
        avatar: "avalonee.jpg"
    };

    var currentUserId = null;

    function decodeAccessToken(token) {
        if (!token) {
            return null;
        }

        var parts = token.split('.');
        if (parts.length !== 3) {
            return null;
        }

        var payload = parts[1].replace(/-/g, '+').replace(/_/g, '/');
        while (payload.length % 4 !== 0) {
            payload += '=';
        }

        try {
            return JSON.parse(atob(payload));
        } catch (error) {
            return null;
        }
    }

    function extractUser(data) {
        if (!data) {
            return null;
        }

        if (Array.isArray(data)) {
            return data.length ? data[0] : null;
        }

        return data;
    }


    function authenticate(email, password) {
        $.ajax({
            url: 'http://localhost:8000/api/login',
            method: 'POST',
            data: {email: email, password: password},
            success: function (response) {
                const accessToken = response.data.accessToken;
                localStorage.setItem('accessToken', accessToken);
                onAuthenticationSuccess('Authentification réussie.');
            },
            error: function () {
                onAuthenticationError('Les informations de connexion sont invalides.');
            }
        });
    }

    function deauthenticate() {
        $.ajax({
            url: 'http://localhost:8000/api/logout',
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
            },
            success: function (response) {
                localStorage.removeItem('accessToken');
                onDeauthentication('Vous avez été déconnecté.');
            },
            error: function () {
                updateMessage('La déconnexion a échoué.');
            }
        });
    }

    function reauthenticate(callback) {
        $.ajax({
            url: 'http://localhost:8000/api/token/refresh',
            method: 'POST',
            success: function (response) {
                const accessToken = response.data.accessToken;
                localStorage.setItem('accessToken', accessToken);
                // TODO Do something if needed
                if (callback) callback(response.data.accessToken);
            },
            error: function () {
                // TODO Do something if needed
            }
        });
    }

    function endpointList() {
        $.ajax({
            url: 'http://localhost:8000/api/user',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
            },
            success: function (response) {
                // TODO Do something if needed
            },
            error: function () {
                // TODO Do something if needed
            }
        });
    }

    function endpointGet(id) {
        $.ajax({
            url: 'http://localhost:8000/api/user' + '/' + id,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
            },
            success: function (response) {
                // TODO Do something if needed
            },
            error: function () {
                // TODO Do something if needed
            }
        });
    }

    function endpointCreate(data) {
        $.ajax({
            url: 'http://localhost:8000/api/user',
            method: 'POST',
            data: data,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
            },
            success: function (response) {
                // TODO Do something if needed
            },
            error: function () {
                // TODO Do something if needed
            }
        });
    }

    function registerUser(data) {
        $.ajax({
            url: 'http://localhost:8000/api/user',
            method: 'POST',
            data: data,
            success: function (response) {
                onRegistrationSuccess('Compte créé avec succès.', response.data);
            },
            error: function (xhr) {
                var message = 'La création du compte a échoué.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                onRegistrationError(message);
            }
        });
    }

    function endpointUpdate(id, data) {
        $.ajax({
            url: 'http://localhost:8000/api/user' + '/' + id,
            method: 'PATCH',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
            },
            success: function (response) {
                // TODO Do something if needed
            },
            error: function () {
                // TODO Do something if needed
            }
        });
    }

    function endpointReplace(id) {
        // TODO Nothing to do
    }

    function endpointDelete(id) {
        $.ajax({
            url: 'http://localhost:8000/api/user' + '/' + id,
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
            },
            success: function (response) {
                // TODO Do something if needed
            },
            error: function () {
                // TODO Do something if needed
            }
        });
    }

    /*
     * Example of a sequence of actions:
     *   - 4
     *   - 1 (with valid credentials)
     *   - 4
     *   - 1 (with invalid credentials)
     *   - 4
     *   - 1 (with valid credentials)
     *   - 4
     *   - 2
     *   - 4
     *   - 1 (with valid credentials, then wait for 5 minutes or change JWT_TTL value)
     *   - 3
     *   - 2
     *   - 3
     */
    const action = 1;
    switch (action) {
        case 1:
            authenticate("lucas.morel@example.com", "password");
            break;
        case 2:
            deauthenticate();
            break;
        case 3:
            reauthenticate();
            break;
        case 4:
            endpointList();
            break;
        case 5:
            endpointGet(1);
            break;
        case 6:
            endpointCreate(dataSample);
            break;
        case 7:
            endpointUpdate(1, partialDataSample);
            break;
        case 8:
            // endpointReplace(1);
            // TODO Nothing to doo
            break;
        case 9:
            endpointDelete(6);
            break;
        default:
            console.log('Unknown action number: ' + action);
            break;
    }

    function fetchUserProfile(userId) {
        $.ajax({
            url: 'http://localhost:8000/api/user/' + userId,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
            },
            success: function (response) {
                var user = extractUser(response.data);
                if (user) {
                    fillProfileForm(user);
                    $('#profile-section').show();
                    updateProfileMessage('');
                } else {
                    updateProfileMessage('Impossible de charger le profil utilisateur.');
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    reauthenticate(function () {
                        fetchUserProfile(userId);
                    });
                    return;
                }
                updateProfileMessage('Impossible de charger le profil utilisateur.');
            }
        });
    }

    function loadAuthenticatedUser() {
        var token = localStorage.getItem('accessToken');
        var payload = decodeAccessToken(token);

        if (!payload || !payload.user_id) {
            return;
        }

        currentUserId = payload.user_id;
        fetchUserProfile(currentUserId);
    }

    function onAuthenticationSuccess(message) {
        $('#auth-form')[0].reset();
        $('#auth-form').hide();
        $('#logout-button').show();
        $('#registration').hide();
        updateMessage(message);
        loadAuthenticatedUser();
    }

    function onAuthenticationError(message) {
        $('#auth-form').show();
        $('#logout-button').hide();
        $('#registration').show();
        updateMessage(message);
        currentUserId = null;
        $('#profile-section').hide();
        clearProfileForm();
    }

    function onDeauthentication(message) {
        $('#auth-form')[0].reset();
        $('#auth-form').show();
        $('#logout-button').hide();
        $('#registration').show();
        updateMessage(message);
        currentUserId = null;
        $('#profile-section').hide();
        clearProfileForm();
    }

    function updateMessage(message) {
        $('#auth-message').text(message);
    }

    function onRegistrationSuccess(message, data) {
        $('#register-form')[0].reset();
        var createdUser = extractUser(data);
        if (createdUser && createdUser.email) {
            $('#auth-email').val(createdUser.email);
        }
        updateRegisterMessage(message);
    }

    function onRegistrationError(message) {
        updateRegisterMessage(message);
    }

    function updateRegisterMessage(message) {
        $('#register-message').text(message);
    }

    function fillProfileForm(user) {
        $('#profile-pseudo').val(user.pseudo || '');
        if (user.gender) {
            $('#profile-gender').val(user.gender);
        } else {
            $('#profile-gender')[0].selectedIndex = 0;
        }
        $('#profile-avatar').val(user.avatar || '');
    }

    function clearProfileForm() {
        $('#profile-form')[0].reset();
        $('#profile-gender')[0].selectedIndex = 0;
        updateProfileMessage('');
    }

    function updateProfileMessage(message) {
        $('#profile-message').text(message);
    }

    function updateUserProfile(data) {
        if (!currentUserId) {
            updateProfileMessage('Aucun utilisateur authentifié.');
            return;
        }

        $.ajax({
            url: 'http://localhost:8000/api/user/' + currentUserId,
            method: 'PATCH',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('accessToken'),
            },
            success: function (response) {
                var user = extractUser(response.data);
                if (user) {
                    fillProfileForm(user);
                }
                onProfileUpdateSuccess('Profil mis à jour.', user);
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    reauthenticate(function () {
                        updateUserProfile(data);
                    });
                    return;
                }

                var message = 'La mise à jour du profil a échoué.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                onProfileUpdateError(message);
            }
        });
    }

    function onProfileUpdateSuccess(message) {
        updateProfileMessage(message);
    }

    function onProfileUpdateError(message) {
        updateProfileMessage(message);
    }

    $(function () {
        $('#auth-form').on('submit', function (event) {
            event.preventDefault();
            var email = $('#auth-email').val();
            var password = $('#auth-password').val();
            authenticate(email, password);
        });

        $('#logout-button').on('click', function () {
            deauthenticate();
        });

        $('#register-form').on('submit', function (event) {
            event.preventDefault();
            var registrationData = {
                first_name: $('#register-first-name').val(),
                last_name: $('#register-last-name').val(),
                pseudo: $('#register-pseudo').val(),
                birth_date: $('#register-birth-date').val(),
                gender: $('#register-gender').val(),
                avatar: $('#register-avatar').val(),
                email: $('#register-email').val(),
                password: $('#register-password').val(),
            };
            registerUser(registrationData);
        });

        $('#profile-form').on('submit', function (event) {
            event.preventDefault();

            var updateData = {};
            var pseudo = $('#profile-pseudo').val();
            var gender = $('#profile-gender').val();
            var avatar = $('#profile-avatar').val();

            if (pseudo) {
                updateData.pseudo = pseudo;
            }
            if (gender) {
                updateData.gender = gender;
            }
            if (avatar) {
                updateData.avatar = avatar;
            }

            if ($.isEmptyObject(updateData)) {
                updateProfileMessage('Veuillez renseigner au moins un champ pour mettre à jour votre profil.');
                return;
            }

            updateUserProfile(updateData);
        });

        if (localStorage.getItem('accessToken')) {
            onAuthenticationSuccess('Session restaurée.');
        }
    });
</script>
</body>
</html>
