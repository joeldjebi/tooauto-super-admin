</div>
<!-- Footer Start -->
</div>

</div>
<!-- ============ Search UI End ============= -->
<script src="../../dist-assets/js/plugins/jquery-3.3.1.min.js"></script>
<script src="../../dist-assets/js/plugins/bootstrap.bundle.min.js"></script>
<script src="../../dist-assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../../dist-assets/js/scripts/script.min.js"></script>
<script src="../../dist-assets/js/scripts/sidebar.large.script.min.js"></script>
<script src="../../dist-assets/js/plugins/echarts.min.js"></script>
<script src="../../dist-assets/js/scripts/echart.options.min.js"></script>
<script src="../../dist-assets/js/scripts/dashboard.v1.script.min.js"></script>
<script src="../../dist-assets/js/scripts/customizer.script.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<script src="../../dist-assets/js/plugins/datatables.min.js"></script>
<script src="../../dist-assets/js/scripts/datatables.script.min.js"></script>
<script src="../../dist-assets/js/scripts/customizer.script.min.js"></script>
<script>
    $(document).ready(function(){
        $("#delete").click(function(e){
            if(!confirm('Are you sure?')){
                e.preventDefault();
                return false;
            }
            return true;
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Lorsqu'on clique sur le bouton "Ajouter Chapitre"
        $('#addEpisodeBtn').click(function(e) {
            e.preventDefault();

            // Cloner l'élément caché (modèle)
            var newChapitre = $('#addChapitre').clone();

            // Retirer l'ID pour éviter les conflits, puis afficher l'élément
            newChapitre.removeAttr('id').show();

            // Réinitialiser les champs de texte et sélectionner "Télécharger" par défaut
            newChapitre.find('input, textarea').val('');
            newChapitre.find('select').val('upload_chapitre');
            newChapitre.find('.file_group_chapitre').show();
            newChapitre.find('.link_group_chapitre').hide();

            // Ajouter le nouveau chapitre au conteneur
            $('#chapitresContainer').append(newChapitre);
        });

        // Suppression d'un chapitre
        $(document).on('click', '.removeChapitreBtn', function(e) {
            e.preventDefault();

            // Supprimer l'élément du DOM
            $(this).closest('.chapitre-item').remove();
        });

        // Gestion du changement de type de fichier pour afficher le bon champ
        $(document).on('change', '.file_type_chapitre', function() {
            var fileGroup = $(this).closest('.col-md-6').find('.file_group_chapitre');
            var linkGroup = $(this).closest('.col-md-6').find('.link_group_chapitre');

            if ($(this).val() === 'upload_chapitre') {
                fileGroup.show();
                linkGroup.hide();
            } else {
                fileGroup.hide();
                linkGroup.show();
            }
        });
    });
</script>


</body>
</html>