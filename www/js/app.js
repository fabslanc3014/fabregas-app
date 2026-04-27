'use strict';
/* ── app.js ── */
$(document).ready(function () {

    const App = {
        canvas: $("#app"),
        api: "https://m.gohumano.com/apislim4lance/",
        usertype: localStorage.getItem("usertype"),
        token: localStorage.getItem("token"),

        getToken: function () {
            return localStorage.getItem("token");
        },
        setToken: function (token) {
            localStorage.setItem("token", token);
            this.token = token;
        },
        removeToken: function () {
            localStorage.removeItem("token");
            localStorage.removeItem("usertype");
            localStorage.removeItem("username");
            this.token = null;
        },
        getUsername: function () {
            return localStorage.getItem("username");
        },
        setUsername: function (username) {
            localStorage.setItem("username", username);
        },
        setUserType: function (usertype) {
            localStorage.setItem("usertype", usertype);
            this.usertype = usertype;
        },
        authenticate: function () {
            var token = this.getToken();
            return token !== null && token !== "";
        },

        fetchUser: function (callback) {
            var username = this.getUsername();
            if (!username) {
                console.warn("fetchUser: no username in localStorage");
                callback(null);
                return;
            }

            $.ajax({
                url: App.api + "users/" + username,  // ✅ fixed
                method: "GET",
                contentType: "application/json",
                success: function (res) {
                    if (res && res.success && res.user) {
                        callback(res.user);
                    } else {
                        console.warn("fetchUser: server returned failure —", res);
                        callback(null);
                    }
                },
                error: function (xhr, status, err) {
                    console.error("fetchUser AJAX error:", xhr.status, xhr.responseText, err);
                    callback(null);
                }
            });
        },

        computeAge: function (birthday) {
            var dob = new Date(birthday);
            var today = new Date();
            var years = today.getFullYear() - dob.getFullYear();
            var months = today.getMonth() - dob.getMonth();
            var days = today.getDate() - dob.getDate();
            if (days < 0) {
                months--;
                var lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                days += lastMonth.getDate();
            }
            if (months < 0) { years--; months += 12; }
            return {
                years: years,
                months: months,
                days: days,
                display: years + " yrs, " + months + " mos, " + days + " days"
            };
        },

        initialize: function () { }
    };

    function bindLogout() {
        $(document).off("click", "#logoutBtn").on("click", "#logoutBtn", function () {
            App.removeToken();
            window.location.hash = "#/login/";
        });
    }

    function setField(id, isOk, message) {
        $("#" + id).removeClass("input-ok input-error").addClass(isOk ? "input-ok" : "input-error");
        $("#msg-" + id).text(message).removeClass("ok error").addClass(isOk ? "ok" : "error");
    }

    $.Mustache.options.warnOnMissingTemplates = true;

    $.Mustache.load("templates/template.html").done(function () {

        /* ── #/login/ ── */
        Path.map("#/login/").to(function () {

            if (App.authenticate()) {
                window.location.hash = "#/home/";
                return;
            }

            App.canvas.html("").append($.Mustache.render("loginForm"));

            $("#loginform").off("submit").on("submit", function (e) {
                e.preventDefault();

                var inputUser = $("#loginUsername").val().trim();
                var inputPass = $("#loginPassword").val();
                var $alert = $("#loginAlert");

                if (!inputUser) {
                    setField("loginUsername", false, "Username is required.");
                    return;
                } else {
                    setField("loginUsername", true, "Good");
                }

                if (!inputPass) {
                    setField("loginPassword", false, "Password is required.");
                    return;
                } else {
                    setField("loginPassword", true, "Good");
                }

                $.ajax({
                    url: App.api + "ajax/login",  // ✅ fixed
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ username: inputUser, password: inputPass }),
                    success: function (res) {
                        if (res.success) {
                            var token = guid();
                            App.setToken(token);
                            App.setUsername(res.user.username);
                            App.setUserType(res.user.usertype || "user");

                            $alert.removeClass("alert-error").addClass("alert-success")
                                .text("Welcome back, " + res.user.username + "! Redirecting...").show();

                            setTimeout(function () {
                                window.location.hash = "#/home/";
                            }, 1000);
                        } else {
                            $alert.removeClass("alert-success").addClass("alert-error")
                                .text(res.message).show();
                        }
                    },
                    error: function () {
                        $alert.removeClass("alert-success").addClass("alert-error")
                            .text("Server error. Please try again.").show();
                    }
                });
            });
        });

        /* ── #/sign-up/ ── */
        Path.map("#/sign-up/").to(function () {

            if (App.authenticate()) {
                window.location.hash = "#/home/";
                return;
            }

            App.canvas.html("").append($.Mustache.render("signupForm"));

            function validateForm(f) {
                var allValid = true;

                if (!f.username) { setField("username", false, "Username is required."); allValid = false; }
                else { setField("username", true, "Good"); }

                if (!f.full_name) { setField("full_name", false, "Full Name is required."); allValid = false; }
                else { setField("full_name", true, "Good"); }

                if (!f.nickname) { setField("nickname", false, "Nickname is required."); allValid = false; }
                else { setField("nickname", true, "Good"); }

                if (!f.address) { setField("address", false, "Address is required."); allValid = false; }
                else { setField("address", true, "Good"); }

                if (!f.birthday) {
                    setField("birthday", false, "Birthday is required."); allValid = false;
                } else if (new Date(f.birthday) >= new Date()) {
                    setField("birthday", false, "Birthday must be a past date."); allValid = false;
                } else {
                    setField("birthday", true, "Good");
                }

                if (!f.contact) {
                    setField("contact", false, "Contact No. is required."); allValid = false;
                } else if (!/^\d{10,11}$/.test(f.contact)) {
                    setField("contact", false, "Must be 10–11 digits, numbers only."); allValid = false;
                } else {
                    setField("contact", true, "Good");
                }

                if (!f.email) {
                    setField("email", false, "Email is required."); allValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.email)) {
                    setField("email", false, "Enter a valid email address."); allValid = false;
                } else {
                    setField("email", true, "Good");
                }

                if (!f.password) {
                    setField("password", false, "Password is required."); allValid = false;
                } else if (f.password.length < 8) {
                    setField("password", false, "Password must be at least 8 characters."); allValid = false;
                } else {
                    setField("password", true, "Good");
                }

                return allValid;
            }

            function buildUserFields(userObj) {
                return Object.keys(userObj).map(function (key) {
                    return {
                        key: key,
                        value: key === "password" ? "••••••••" : userObj[key],
                        isAge: key === "age"
                    };
                });
            }

            function loadAndRenderUsers() {
                $.getJSON(App.api + "users", function (res) {  // ✅ fixed
                    if (!res.success || res.users.length === 0) return;

                    var users = res.users.map(function (userObj, index) {
                        return {
                            index: index + 1,
                            realIndex: index,
                            username: userObj.username,
                            fields: buildUserFields(userObj)
                        };
                    });
                    $("#usersLogContent").html(
                        $.Mustache.render("template-users-log", { users: users })
                    );
                    $("#usersLog").show();

                }).fail(function () {
                    console.error("Could not load users from server.");
                });
            }
            loadAndRenderUsers();

            $(document).off("click", ".btn-remove").on("click", ".btn-remove", function () {
                var username = $(this).data("username");

                if (!confirm('Are you sure you want to delete "' + username + '"? This cannot be undone.')) {
                    return;
                }

                $.ajax({
                    url: App.api + "users/delete",  // ✅ fixed
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ username: username }),
                    success: function (res) {
                        if (res.success) {
                            loadAndRenderUsers();
                            $.getJSON(App.api + "users", function (r) {  // ✅ fixed
                                if (r.success) allUsersCache = r.users;
                            });
                        } else {
                            alert("Could not delete user: " + res.message);
                        }
                    },
                    error: function () {
                        alert("Server error. Please try again.");
                    }
                });
            });

            var allUsersCache = [];

            $.getJSON(App.api + "users", function (res) {  // ✅ fixed
                if (res.success && res.users.length) {
                    allUsersCache = res.users;
                }
            });

            function runSearch() {
                var query = $("#userSearchInput").val().trim().toLowerCase();
                var $results = $("#searchResults");

                if (!query) {
                    $results.hide().html("");
                    return;
                }

                var matches = allUsersCache.filter(function (u) {
                    return (
                        (u.username  || "").toLowerCase().includes(query) ||
                        (u.full_name || "").toLowerCase().includes(query) ||
                        (u.nickname  || "").toLowerCase().includes(query) ||
                        (u.email     || "").toLowerCase().includes(query)
                    );
                });

                if (!matches.length) {
                    $results.html('<p class="search-no-result">No users found.</p>').show();
                    return;
                }

                var html = matches.map(function (u) {
                    return (
                        '<div class="search-result-item">' +
                        '<strong>' + u.username + '</strong>' +
                        '<span>' + u.full_name + ' &bull; ' + u.email + '</span>' +
                        '<span>' + u.address + ' &bull; ' + u.contact + '</span>' +
                        '</div>'
                    );
                }).join("");

                $results.html(html).show();
            }

            $(document).off("click", "#userSearchBtn").on("click", "#userSearchBtn", function () {
                runSearch();
            });

            $("#signupform").off("submit").on("submit", function (e) {
                e.preventDefault();

                var formValues = {
                    username:  $("#username").val().trim(),
                    full_name: $("#full_name").val().trim(),
                    nickname:  $("#nickname").val().trim(),
                    address:   $("#address").val().trim(),
                    birthday:  $("#birthday").val(),
                    contact:   $("#contact").val().trim(),
                    email:     $("#email").val().trim(),
                    password:  $("#password").val()
                };

                console.log("Signup form values:", formValues);

                var $alertBox = $("#submitAlert");
                if (!validateForm(formValues)) {
                    $alertBox.removeClass("alert-success").addClass("alert-error")
                        .text("Please fix the errors and missing fields before submitting.").show();
                    $("#outputContainer").hide();
                    return;
                }

                var age = App.computeAge(formValues.birthday);

                $.ajax({
                    url: App.api + "ajax/register",  // ✅ fixed
                    method: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({
                        username:  formValues.username,
                        full_name: formValues.full_name,
                        nickname:  formValues.nickname,
                        address:   formValues.address,
                        birthday:  formValues.birthday,
                        age:       age.display,
                        contact:   formValues.contact,
                        email:     formValues.email,
                        password:  formValues.password
                    }),
                    success: function (res) {
                        if (res.success) {
                            $alertBox.removeClass("alert-error").addClass("alert-success")
                                .text("Account created successfully! Welcome, " + formValues.username + "!").show();

                            var displayObj = {
                                username:  formValues.username,
                                full_name: formValues.full_name,
                                nickname:  formValues.nickname,
                                address:   formValues.address,
                                birthday:  formValues.birthday,
                                age:       age.display,
                                contact:   formValues.contact,
                                email:     formValues.email,
                                password:  formValues.password
                            };

                            $("#outputContent").html(
                                $.Mustache.render("template-output-fields", { fields: buildUserFields(displayObj) })
                            );
                            $("#outputContainer").show();

                            loadAndRenderUsers();

                            $.getJSON(App.api + "users", function (r) {  // ✅ fixed
                                if (r.success) allUsersCache = r.users;
                            });

                            $("#signupform")[0].reset();
                            $(".container input").removeClass("input-ok input-error");
                            $(".msg").removeClass("ok error").hide();

                        } else {
                            $alertBox.removeClass("alert-success").addClass("alert-error")
                                .text(res.message).show();
                        }
                    },
                    error: function () {
                        $alertBox.removeClass("alert-success").addClass("alert-error")
                            .text("Server error. Please try again.").show();
                    }
                });
            });
        });

        /* ── #/home/ ── */
        Path.map("#/home/").to(function () {

            if (!App.authenticate()) {
                window.location.hash = "#/login/";
                return;
            }

            var username = App.getUsername() || "User";
            App.canvas.html("").append($.Mustache.render("homePage", {
                loggedIn: true,
                username: username
            }));

            bindLogout();
        });

        /* ── #/profile/ ── */
        Path.map("#/profile/").to(function () {

            if (!App.authenticate()) {
                window.location.hash = "#/login/";
                return;
            }

            App.canvas.html("").append($.Mustache.render("profilePage"));
            bindLogout();

            var $alert = $("#profileAlert");

            App.fetchUser(function (user) {

                if (!user) {
                    $alert.removeClass("alert-success").addClass("alert-error")
                        .text("Could not load profile from server.").show();
                    $("#p_username").val(App.getUsername() || "");
                    return;
                }

                $("#p_username").val(user.username  || "");
                $("#p_full_name").val(user.full_name || "");
                $("#p_nickname").val(user.nickname  || "");
                $("#p_address").val(user.address    || "");
                $("#p_birthday").val(user.birthday  || "");
                $("#p_contact").val(user.contact    || "");
                $("#p_email").val(user.email        || "");

                $("#profileForm").off("submit").on("submit", function (e) {
                    e.preventDefault();

                    var allValid = true;

                    var updated = {
                        username:     $("#p_username").val().trim(),
                        full_name:    $("#p_full_name").val().trim(),
                        nickname:     $("#p_nickname").val().trim(),
                        address:      $("#p_address").val().trim(),
                        birthday:     $("#p_birthday").val(),
                        contact:      $("#p_contact").val().trim(),
                        email:        $("#p_email").val().trim(),
                        new_password: $("#p_new_password").val()
                    };

                    if (!updated.username) { setField("p_username", false, "Username is required."); allValid = false; }
                    else { setField("p_username", true, "Good"); }
                    if (!updated.full_name) { setField("p_full_name", false, "Full Name is required."); allValid = false; }
                    else { setField("p_full_name", true, "Good"); }
                    if (!updated.nickname) { setField("p_nickname", false, "Nickname is required."); allValid = false; }
                    else { setField("p_nickname", true, "Good"); }
                    if (!updated.address) { setField("p_address", false, "Address is required."); allValid = false; }
                    else { setField("p_address", true, "Good"); }

                    if (!updated.birthday) {
                        setField("p_birthday", false, "Birthday is required."); allValid = false;
                    } else if (new Date(updated.birthday) >= new Date()) {
                        setField("p_birthday", false, "Must be a past date."); allValid = false;
                    } else {
                        setField("p_birthday", true, "Good");
                    }

                    if (!updated.contact) {
                        setField("p_contact", false, "Contact is required."); allValid = false;
                    } else if (!/^\d{10,11}$/.test(updated.contact)) {
                        setField("p_contact", false, "Must be 10–11 digits."); allValid = false;
                    } else {
                        setField("p_contact", true, "Good");
                    }

                    if (!updated.email) {
                        setField("p_email", false, "Email is required."); allValid = false;
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(updated.email)) {
                        setField("p_email", false, "Enter a valid email."); allValid = false;
                    } else {
                        setField("p_email", true, "Good");
                    }

                    if (updated.new_password && updated.new_password.length < 8) {
                        setField("p_new_password", false, "Password must be at least 8 characters.");
                        allValid = false;
                    } else if (updated.new_password) {
                        setField("p_new_password", true, "Good");
                    }

                    if (!allValid) {
                        $alert.removeClass("alert-success").addClass("alert-error")
                            .text("Please fix the errors before saving.").show();
                        return;
                    }

                    var age = App.computeAge(updated.birthday);

                    $.ajax({
                        url: App.api + "users/" + user.username + "/update",  // ✅ fixed
                        method: "POST",
                        contentType: "application/json",
                        data: JSON.stringify({
                            original_username: user.username,
                            username:          updated.username,
                            full_name:         updated.full_name,
                            nickname:          updated.nickname,
                            address:           updated.address,
                            birthday:          updated.birthday,
                            age:               age.display,
                            contact:           updated.contact,
                            email:             updated.email,
                            new_password:      updated.new_password
                        }),
                        success: function (res) {
                            if (res.success) {
                                App.setUsername(res.user.username);
                                user = res.user;

                                $alert.removeClass("alert-error").addClass("alert-success")
                                    .text("Profile updated successfully!").show();

                                $("#p_new_password").val("");

                            } else {
                                $alert.removeClass("alert-success").addClass("alert-error")
                                    .text(res.message).show();
                            }
                        },
                        error: function () {
                            $alert.removeClass("alert-success").addClass("alert-error")
                                .text("Server error. Please try again.").show();
                        }
                    });
                });
            });
        });

        Path.rescue(function () {
            App.canvas.html("<h2 style='text-align:center;margin-top:80px;'>404 — Page not found.</h2>");
        });
        Path.root("#/login/");
        Path.listen();
    });
});