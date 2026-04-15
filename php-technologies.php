4.1) PHP

4.1.1) Frameworky

PHP nepoužívalo žádnou knihovnu. Vše bylo postaveno na “vanilla” PHP.

Byl implementován objektově orientovaný pattern pro PHP implementaci. Vždy je základní třída jako (Login, Signup, Admin...), které slouží jako jednochá nástavba pro PDO. 		Pak jsou tu kontrolní třídy (mají v názvu “contr”), které rozšiřují základní třídu o validační logiku. Tento pattern pomáhá separovat business a validační logiku, což obecně bez ohledu na jazyk je dobrá praktika.

4.1.2) Bezpečnost

Všechny databázové operace jsou kompletně používají PDO interface s předpřipravenými dotazy. To znamená, že vstup od uživatele je vždy posílán jako separátní parametr a nikdy není připojen k samotnému SQL řetězci znaků. Tato technika slouží jako obrana proti SQL injekcím

// Zde je vidět implementace obrany proti SQL injection — uživatelský vstup
// je předán jako parametr [], nikdy vložen přímo do SQL řetězce.
// (classes/login.classes.php)
$stmt = $this->connect()->prepare('SELECT * FROM users WHERE users_email = ?');
$stmt->execute([$identifier]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Zde je vidět nastavení SSL/TLS při vytváření PDO spojení.
// sslmode=verify-ca zajistí, že je ověřena identita serveru pomocí ca.pem.
// (classes/dbh.classes.php)
$conn  = "mysql:host=" . $fields["host"];
$conn .= ";port=" . $fields["port"];
$conn .= ";dbname=defaultdb";
$conn .= ";sslmode=verify-ca;sslrootcert='ca.pem'";
$dbh = new PDO($conn, $fields["user"], $fields["pass"]);

// Zde je vidět hashování hesla při registraci — čistý text se nikdy neuloží.
// (classes/signup.classes.php)
$hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
$stmt->execute([$username, $hashedPwd, $email, $petsname]);

// Zde je vidět ověření hesla při přihlášení — password_verify() porovnává
// (classes/login.classes.php)
if (!password_verify($pwd, $user["users_pwd"])) {
    header("Location: ../index.php?error=wrongpassword");
    exit();
}

// Zde je vidět kontrola admin oprávnění na vstupním bodě pro ban akci.
// (includes/ban.inc.php)
if (empty($_SESSION['userid']) || empty($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    header("Location: ../index.php?error=notauthorized");
    exit();
}

// Podmínka AND is_admin = 0 v SQL je druhá vrstva ochrany — chrání i kdyby session check selhal.
// (classes/admin.classes.php)
$stmt = $this->connect()->prepare(
    'UPDATE users SET is_banned = 1 WHERE users_id = ? AND is_admin = 0'
);
return $stmt->execute([(int) $userId]);

// Zde je vidět validace emailu pomocí PHP filtru.
// (classes/signup-contr.classes.php)
if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../signup.php?error=invalidemail");
    exit();
}

// Zde je vidět whitelist validace pro pole emoce — in_array() se strict=true
// odmítne cokoliv, co není přesně na seznamu.
// (classes/checkin-contr.classes.php)
private static $validEmotions = ['joy', 'sadness', 'anger', 'calmness', 'neutral', 'anxiety'];

private function validEmotion() {
    return in_array($this->emotion, self::$validEmotions, true);
}

// Zde je vidět ochrana proti XSS — htmlspecialchars() obaluje veškerý
// uživatelský obsah před vykreslením do HTML.
// (admin.php)
<td><?= htmlspecialchars($u['username']) ?></td>
<td><?= htmlspecialchars($u['users_email']) ?></td>

// Zde je vidět POST-only ochrana — bez přítomnosti pole "submit"
// soubor pouze přesměruje a žádnou akci neprovede.
// (includes/login.inc.php)
if (isset($_POST["submit"])) {
    $identification = $_POST["email"];
    $pwd = $_POST["password"];
    // ... zpracování přihlášení
}

4.2 Javascript a HTML

4.2.1 Frameworky

Znovu nebyli použity žádné frameworky nebo knihovny. Veškerý kód je psán v JavaSript standardu, který je podle ES6 a výše. ES6 přinesl klíčová slova jako const a let. Let nahradilo var a const přidalo možnost mít jasně nemění se hodnoty.

4.2.2 Bezpečnost


// Zde je vidět implementace whitelist přístupu — URL parametr nikdy není
// vložen do stránky přímo. Použije se jen jako klíč do #messages mapy.
// (js/alerts.js)
static #messages = {
    emptyinput:       "Please fill in all fields!",
    usernotfound:     "User not found.",
    wrongpassword:    "Incorrect password.",
    banned:           "Your account has been suspended.",
    // ...
};

const error = params.get("error");       // hodnota z URL
const message = this.#messages[error];   // jen schválený text se zobrazí
if (message) this.#showBanner(errorEl, message);

// URL se ihned vyčistí, aby parametr nezůstal viditelný.
window.history.replaceState({}, document.title, window.location.pathname);

// Zde je vidět klientská validace hesla — délka, velké písmeno, speciální znak.
// (js/auth.js)
function checkPasswordRules(val) {
    return {
        length:  val.length >= 8,
        upper:   /[A-Z]/.test(val),
        special: /[^A-Za-z0-9]/.test(val),
    };
}

signupForm.addEventListener('submit', function(e) {
    const rules = checkPasswordRules(password);
    if (!rules.length || !rules.upper || !rules.special) {
        e.preventDefault();   // formulář se neodešle
        updateRequirements();
        return;
    }
});


//autocomplete
// (index.php)
<input type="password" name="password" autocomplete="current-password" ...>

// (signup.php)
<input type="password" name="password" autocomplete="new-password" ...>
<input type="text"     name="petsname" autocomplete="off" ...>


// Zde je vidět fetch() volání a použití escHtml() při vkládání dat do DOM.
// Bez escHtml() by uživatelské jméno obsahující <script> mohlo způsobit XSS.
// (js/admin.js)
fetch('includes/api/active-users.inc.php')
    .then(r => r.json())
    .then(users => {
        tbody.innerHTML = users.map(u =>
            '<tr><td>' + escHtml(u.username) + '</td></tr>'
        ).join('');
    })
    .catch(() => { /* tiché ignorování přechodných chyb */ });

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
