     <div class="modal-content-div">
       <form id="change-password-form" method="post">

         <h1 class="change-password-title">Change Password</h1>
         <label for="old-password">Old Password</label>
         <input type="password" name="old-password" id="old-password" class="pass-input"
           required>
         <p class="pass-text old-pass"></p>

         <label for="new-password">New Password</label>
         <input type="password"
           name="new-password"
           id="new-password"
           autocomplete="off"
           class="pass-input"
           required>
         <p class="pass-text pass"></p>

         <label for="new-password-confirm">Confirm New Password</label>
         <input type="password"
           name="new-password-confirm"
           id="new-password-confirm"
           autocomplete="off"
           class="pass-input"
           required />
         <p class="pass-text conf-pass"></p>
         <button type="submit" class="change-password-submit">Change Password</button>

       </form>
     </div>