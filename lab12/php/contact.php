<?php
class Contact {


    /*
     * PokazKontakt
     * Wyświetla formularz kontaktowy.
     * Formularz składa się z pola wprowadzenia emaila, tytułu i treści wiadomości.
     * Po przesłaniu formularza, email z hasłem zostanie wysłany na podany adres.
     */
    function PokazKontakt(){
        $wynik = '
        <div class="contact">
            <form method = "post" name="LoginForm" enctype="multipart/form-data" action"'.$_SERVER['REQUEST_URI'].'">
                <table class="contact">
                    <tr><td class="zaw">Email: </td><td><input type="text"  class="contact" /></td></tr>
                    <tr><td class="zaw">Tytuł: </td><td><input type="text"  class="contact" /></td></tr>
                    <tr>
                    <td>Zawartość:</td>
                    <td class="zaw"><textarea></textarea></td>
                    </tr>
                    <tr><td></td><td><input type="submit" class="send-button" value="wyślij" /></td></tr>
                </table>
            </form>
        </div>
        
        ';
        return $wynik;
    }



    /*
     * PokazHaslo
     * Wyświetla formularz odzyskiwania hasła.
     * Formularz składa się z pola wprowadzenia emaila i przycisku wyślij.
     * Po przesłaniu formularza, email z hasłem zostanie wysłany na podany adres.
     */
    function PokazHaslo(){
        $wynik = '
        <div class="passrecov">
			<div class="passrecov">
				<form method="post" name="LoginForm" enctype="multipart/form-data" action="'.$_SERVER['REQUEST_URI'].'">
					<table class="passrecov">
						<tr><td class="log4_t">Email: </td><td><input type="text" name="email_recov" class="passrecov" /></td></tr>
						<tr><td></td><td><input type="submit" name="x1_submit" class="passrecov" value="wyślij" /></td></tr>
					</table>
				</form>
			 </div>
		</div>
		';
		return $wynik;
    }


    /*
     * WyslijMailKontakt
     * Funkcja obsługująca kontakt z formularza.
     * Sprawdza, czy został wprowadzony email, tytuł i treść wiadomości.
     * Jeśli nie, to wyświetla pole emaila, tytuł i treść wiadomości do wypełnienia.
     * Jeśli tak, to wysyła email z hasłem na podany adres email.
     */
    function WyslijMailKontakt($odbiorca){
        if(empty($_POST['email']) || empty($_POST['title']) || empty($_POST['content'])) {
            echo $this->PokazKontakt();
        }
        else {
            $mail['subject']   = htmlspecialchars($_POST['temat'], ENT_QUOTES);
            $mail['body']      = htmlspecialchars($_POST['tresc'], ENT_QUOTES);
            $mail['sender']    = htmlspecialchars($_POST['email'], ENT_QUOTES);
            $mail['recipient'] = $odbiorca;
            
            $header  = "From: Fromularz kontaktowy <".$mail['sender'].">\n";
            $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset-utf-8\nContent-Transfer-Encoding: ";
            $header .= "X-Sender: <".$mail['sender'].">\n";
			$header .= "X-Sender: <".$mail['sender'].">\n";
            $header .= "X-Mailer: prapwww mail 1.2\n";
            $header .= "X-Priority: 3\n";
            $header .= "Return-Path: <".$mail['sender'].">\n";
			$header .= "X-Mailer: prapwww mail 1.2\n";
			$header .= "X-Priority: 3\n";
			$header .= "Return-Path: <".$mail['sender'].">\n";

            mail($mail['recipient'],$mail['subject'],$mail['body'], $header);

            echo 'Wiadomość wysłana';
        }
    }



    
    
    

    /*
     * PrzypomnijHaslo
     * Funkcja obsługująca odzyskiwanie hasła.
     * Sprawdza, czy został wprowadzony email.
     * Jeśli nie, to wyświetla pole emaila do wypełnienia.
     * Jeśli tak, to wysyła email z hasłem na podany adres email.
     */
    function PrzypomnijHaslo($odbiorca){
        if(empty($_POST['email_recovery'])) {	
            echo $this->PokazHaslo();	
		}

        else {
			$mail['sender']			= htmlspecialchars($_POST['email_recovery'], ENT_QUOTES);
			$mail['subject']		= htmlspecialchars("Password Recovery", ENT_QUOTES);
			$mail['body']			= htmlspecialchars("Password = haslo", ENT_QUOTES);

			$mail['recipient']		= $odbiorca;
			
			$header  = "From: Forumularz kontaktowy <".$mail['sender'].">\n";
			$header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset-utf-8\nContent-Transfer-Encoding: ";
			$header .= "X-Sender: <".$mail['sender'].">\n";
			$header .= "X-Mailer: prapwww mail 1.2\n";
			$header .= "X-Priority: 3\n";
			$header .= "Return-Path: <".$mail['sender'].">\n";
			
			
			echo 'Hasło wysłane';
		}

    }
}
?>