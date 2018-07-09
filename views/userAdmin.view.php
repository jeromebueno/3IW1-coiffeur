
    <div>
        <article class="arriere_plan">

            <div class="col-s-12 col-m-8 col-l-12 form_register_admin">

                <div class="col-l-4">
                    <h2 class="center title"> Gestion des utilisateurs</h2>
                </div>

            </div>

            <div class="col-s-12 col-l-12 col-m-9 tab-admin">
                <table class="userManagerTab col-l-12">
                    <tr>
                        
                        <th id="nom">Nom Complet</th>
                        <th id="email">Email</th>
                        <th id="tel">Téléphone</th>
                        <th id="status">Status</th>
                        <th >Modifier</th>
                        <th >Supprimer</th>
                        <th >Supprimer en base</th>

                    </tr>
                    <?php   
                    
                            foreach ($u as $user) {

                                
                                echo "
                                 <tr>
                                    <td> ".  $user->getFirstname() . " " . $user->getLastname() ."
                                    </td>
                                    <td>". $user->getEmail() . "</td>
                                    <td>". $user->getTel() . "</td>
                                    <td> ". $arrayStatus[$user->getStatus()] ."</td>
                                    <td><a href='modifyUser?id=" . $user->getId() ."' class='buttonUserModify'>Modifier</a></td>
                                    <td><a href='deleteUser?id=" . $user->getId() ."' class='buttonUserDelete'>Supprimer</a></td>
                                    <td><a href='delete?id=" . $user->getId() ."' class='buttonUserDeleteBd'>Droit a l'oubli</a></td>
                                </tr>
                                ";
                            }
                    
                    ?>
                    
                    <nav aria-label="navigation">
                        <tr class="page">
                            <td class="previous"><a href="#" title="Précédent">Précédent</a></td>
                            <td class="page center" colspan="5">1/104</td>
                            <td class="next-admin"><a href="#" title="Suivant">Suivant</a></td>
                        </tr>
                       
                    </nav>
                </table>
                
            </div>
        </article>
        <a href=" <?php echo DIRNAME;?>admin/addUser"  class="buttonUserAdd">Ajouter un utilisateur</a>
    </div>

  </main>

<?php

include "templates/footer.tpl.php";
?>

