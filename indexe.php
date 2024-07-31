<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>axeh</title>
    <style id="dynamicStyle"></style>
    <script>
        let currentBackgroundColor = '';

        function toggleColorblindMode() {
            let select = document.getElementById("colorblindType");
            if (select.value === "normal") {
                select.value = "protanopia";
            } else {
                select.value = "normal";
            }
            applyColorblindMode();
        }

        function applyColorblindMode() {
            let filterType = document.getElementById("colorblindType").value;
            let originalColor = hexToRgb(currentBackgroundColor);
            let adjustedColor;

            switch(filterType) {
                case "protanopia":
                    adjustedColor = simulateProtanopia(originalColor);
                    break;
                case "deuteranopia":
                    adjustedColor = simulateDeuteranopia(originalColor);
                    break;
                case "tritanopia":
                    adjustedColor = simulateTritanopia(originalColor);
                    break;
                case "achromatopsia":
                    adjustedColor = simulateAchromatopsia(originalColor);
                    break;
                default:
                    adjustedColor = originalColor;
            }

            let newBackgroundColor = rgbToHex(adjustedColor);
            document.body.style.backgroundColor = newBackgroundColor;
            adjustColors(newBackgroundColor);
        }

        function simulateProtanopia(rgb) {
            return {
                r: 0.567 * rgb.r + 0.433 * rgb.g + 0 * rgb.b,
                g: 0.558 * rgb.r + 0.442 * rgb.g + 0 * rgb.b,
                b: 0 * rgb.r + 0.242 * rgb.g + 0.758 * rgb.b
            };
        }

        function simulateDeuteranopia(rgb) {
            return {
                r: 0.625 * rgb.r + 0.375 * rgb.g + 0 * rgb.b,
                g: 0.7 * rgb.r + 0.3 * rgb.g + 0 * rgb.b,
                b: 0 * rgb.r + 0.3 * rgb.g + 0.7 * rgb.b
            };
        }

        function simulateTritanopia(rgb) {
            return {
                r: 0.95 * rgb.r + 0.05 * rgb.g + 0 * rgb.b,
                g: 0 * rgb.r + 0.433 * rgb.g + 0.567 * rgb.b,
                b: 0 * rgb.r + 0.475 * rgb.g + 0.525 * rgb.b
            };
        }

        function simulateAchromatopsia(rgb) {
            let gray = 0.299 * rgb.r + 0.587 * rgb.g + 0.114 * rgb.b;
            return { r: gray, g: gray, b: gray };
        }

        function adjustColors(bgColor) {
            currentBackgroundColor = bgColor;
            let rgb = hexToRgb(bgColor);
            if (!rgb) {
                console.error("Invalid background color:", bgColor);
                return;
            }
            
            let textColor = invertHexColor(bgColor);
            
            document.body.style.color = textColor;
            
            adjustElementColors('input', bgColor, textColor);
            adjustElementColors('select', bgColor, textColor);
            adjustElementColors('button', bgColor, textColor);

            updateCMYK(bgColor);
        }
        
        function adjustElementColors(selector, bgColor, textColor) {
            let elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                if (el.tagName.toLowerCase() === 'button') {
                    let buttonBg = shadeColor(bgColor, 30);
                    let buttonText = invertHexColor(buttonBg);
                    el.style.backgroundColor = buttonBg;
                    el.style.color = buttonText;
                    el.style.borderColor = shadeColor(buttonBg, -20);
                } else {
                    el.style.backgroundColor = shadeColor(bgColor, 20);
                    el.style.color = textColor;
                    el.style.borderColor = shadeColor(bgColor, -20);
                }
            });
        }

        function shadeColor(color, percent) {
            let R = parseInt(color.substring(1,3),16);
            let G = parseInt(color.substring(3,5),16);
            let B = parseInt(color.substring(5,7),16);

            R = parseInt(R * (100 + percent) / 100);
            G = parseInt(G * (100 + percent) / 100);
            B = parseInt(B * (100 + percent) / 100);

            R = (R<255)?R:255;  
            G = (G<255)?G:255;  
            B = (B<255)?B:255;  

            let RR = ((R.toString(16).length==1)?"0"+R.toString(16):R.toString(16));
            let GG = ((G.toString(16).length==1)?"0"+G.toString(16):G.toString(16));
            let BB = ((B.toString(16).length==1)?"0"+B.toString(16):B.toString(16));

            return "#"+RR+GG+BB;
        }

        function hexToRgb(hex) {
            let shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
            hex = hex.replace(shorthandRegex, function(m, r, g, b) {
                return r + r + g + g + b + b;
            });
            let result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        }

        function rgbToHex(rgb) {
            if (typeof rgb === 'object') {
                return "#" + ((1 << 24) + (Math.round(rgb.r) << 16) + (Math.round(rgb.g) << 8) + Math.round(rgb.b)).toString(16).slice(1);
            }
            if (rgb.startsWith('rgb')) {
                let parts = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
                if (!parts) return '#000000';
                delete(parts[0]);
                for (let i = 1; i <= 3; ++i) {
                    parts[i] = parseInt(parts[i]).toString(16);
                    if (parts[i].length == 1) parts[i] = '0' + parts[i];
                }
                return '#' + parts.join('');
            }
            return rgb;
        }

        function invertHexColor(hex) {
            // Remove the hash at the start if it's there
            hex = hex.replace(/^#/, '');

            // Invert the hex color
            let r = (255 - parseInt(hex.slice(0, 2), 16)).toString(16),
                g = (255 - parseInt(hex.slice(2, 4), 16)).toString(16),
                b = (255 - parseInt(hex.slice(4, 6), 16)).toString(16);

            // Pad each component with zeros if necessary
            r = r.padStart(2, '0');
            g = g.padStart(2, '0');
            b = b.padStart(2, '0');

            return `#${r}${g}${b}`;
        }

        function copyToClipboard() {
            let resultText = document.getElementById("result").innerText;
            navigator.clipboard.writeText(resultText).then(function() {
                let confirmation = document.getElementById("copy-confirmation");
                confirmation.innerText = "Résultat copié !";
                setTimeout(function() {
                    confirmation.innerText = "";
                }, 2000);
            }, function(err) {
                let confirmation = document.getElementById("copy-confirmation");
                confirmation.innerText = "Erreur lors de la copie.";
                setTimeout(function() {
                    confirmation.innerText = "";
                }, 2000);
            });
        }

        function updateCMYK(hex) {
            hex = hex.replace(/^#/, '');
            let r = parseInt(hex.substr(0, 2), 16) / 255;
            let g = parseInt(hex.substr(2, 2), 16) / 255;
            let b = parseInt(hex.substr(4, 2), 16) / 255;
            let k = Math.min(1 - r, 1 - g, 1 - b);
            let c = (1 - r - k) / (1 - k);
            let m = (1 - g - k) / (1 - k);
            let y = (1 - b - k) / (1 - k);
            c = Math.round(c * 100);
            m = Math.round(m * 100);
            y = Math.round(y * 100);
            k = Math.round(k * 100);
            document.getElementById('cmykResult').innerHTML = `CMJN: C=${c}%, M=${m}%, J=${y}%, N=${k}%`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            let bgColor = getComputedStyle(document.body).backgroundColor;
            currentBackgroundColor = rgbToHex(bgColor);
            adjustColors(currentBackgroundColor);
            applyColorblindMode();
        });
    </script>
</head>
<body>
    <header>
        <h1>LAMICEDAXEH</h1>
    </header>
    <main>
        <form method="POST">
            <label for="number">Entrez un nombre :</label>
            <input type="text" name="number" id="number" required>
            <label for="fromBase">Base d'origine :</label>
            <select id="fromBase" name="from_base" class="form-select" required>
                <option value="" disabled selected>Choisissez une base</option>
                <?php for ($i = 2; $i <= 16; $i++): ?>
                    <option value="<?php echo $i; ?>">Base <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <label for="toBase">Base de destination :</label>
            <select id="toBase" name="to_base" class="form-select" required>
                <option value="" disabled selected>Choisissez une base</option>
                <?php for ($i = 2; $i <= 16; $i++): ?>
                    <option value="<?php echo $i; ?>">Base <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" name="convertir">Convertir</button>
        </form>
        <button id="colorblindMode" onclick="toggleColorblindMode()">Mode Daltonien</button>
        <select id="colorblindType" onchange="applyColorblindMode()">
            <option value="normal">Normal Vision</option>
            <option value="protanopia">Protanopia</option>
            <option value="deuteranopia">Deuteranopia</option>
            <option value="tritanopia">Tritanopia</option>
            <option value="achromatopsia">Achromatopsia</option>
        </select>
        <?php
        function convert_base($number, $from_base, $to_base)
        {
            if ($from_base < 2 || $from_base > 16 || $to_base < 2 || $to_base > 16) {
                throw new Exception("Base non supportée. Utilisez une base entre 2 et 16.");
            }
            $decimal = 0;
            $number_length = strlen($number);
            for ($i = 0; $i < $number_length; $i++) {
                $digit = $number[$number_length - $i - 1];
                if (ctype_digit($digit)) {
                    $digit_value = intval($digit);
                } else {
                    $digit_value = ord(strtoupper($digit)) - ord('A') + 10;
                }
                if ($digit_value >= $from_base) {
                    throw new Exception("Le chiffre '$digit' n'est pas valide pour la base $from_base.");
                }
                $decimal += $digit_value * pow($from_base, $i);
            }
            $result = '';
            do {
                $remainder = $decimal % $to_base;
                if ($remainder < 10) {
                    $result = $remainder . $result;
                } else {
                    $result = chr(ord('A') + $remainder - 10) . $result;
                }
                $decimal = intdiv($decimal, $to_base);
            } while ($decimal > 0);
            return $result;
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['convertir'])) {
            try {
                $number = $_POST['number'];
                $from_base = intval($_POST['from_base']);
                $to_base = intval($_POST['to_base']);
                if (strlen($number) == 0) {
                    throw new Exception("Veuillez entrer un nombre.");
                }
                $result = convert_base($number, $from_base, $to_base);
                echo "<div class='result-container'>";
                echo "Base $from_base: $number en base $to_base : <span id='result'>$result</span>";
                echo "<button class='copy-button' onclick='copyToClipboard()'>Copier</button>";
                echo "<div id='copy-confirmation' class='copy-confirmation'></div>";
                echo "</div>";
                $decimal_value = base_convert($result, $to_base, 10);
                $hex_color = str_pad(dechex($decimal_value), 6, '0', STR_PAD_LEFT);
                $hex_color = substr($hex_color, 0, 6);
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        let bgColor = '#$hex_color';
                        currentBackgroundColor = bgColor;
                        document.body.style.backgroundColor = bgColor;
                        adjustColors(bgColor);
                        applyColorblindMode();
                    });
                </script>";
            } catch (Exception $e) {
                echo "Erreur : " . $e->getMessage();
            }
        }
        ?>
        <div id="cmykResult"></div>
    </main>
    <footer><img src="./pied.png" alt="pied"></footer>
</body>
</html>
   
