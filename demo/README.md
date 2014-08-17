mparaiso/simple-rest
====================

##demo application

###requirements

- PHP >= 5.4
- composer

###usage

start server :

    # in root directory
    composer install
    # in demo directory
    php -S localhost:3000 -t web web/index.php

####routes

    Name                                             Method Scheme Host Path
    --------------------------------------------------------------------------------------------------------------
    index                                            GET    ANY    ANY  /.{_format}
    mp_simplerest_note_create                        POST   ANY    ANY  /note.{_format}
    mp_simplerest_note_update                        PUT    ANY    ANY  /note/{id}.{_format}
    mp_simplerest_note_delete                        DELETE ANY    ANY  /note/{id}.{_format}
    mp_simplerest_note_index                         GET    ANY    ANY  /note.{_format}
    mp_simplerest_note_read                          GET    ANY    ANY  /note/{id}.{_format}
    mp_simplerest_category_create                    POST   ANY    ANY  /category.{_format}
    mp_simplerest_category_update                    PUT    ANY    ANY  /category/{id}.{_format}
    mp_simplerest_category_delete                    DELETE ANY    ANY  /category/{id}.{_format}
    mp_simplerest_category_index                     GET    ANY    ANY  /category.{_format}
    mp_simplerest_category_read                      GET    ANY    ANY  /category/{id}.{_format}
    mp_simplerest_category_mp_simplerest_note_create POST   ANY    ANY  /category/{parent_id}/note.{_format}
    mp_simplerest_category_mp_simplerest_note_update PUT    ANY    ANY  /category/{parent_id}/note/{id}.{_format}
    mp_simplerest_category_mp_simplerest_note_delete DELETE ANY    ANY  /category/{parent_id}/note/{id}.{_format}
    mp_simplerest_category_mp_simplerest_note_index  GET    ANY    ANY  /category/{parent_id}/note.{_format}
    mp_simplerest_category_mp_simplerest_note_read   GET    ANY    ANY  /category/{parent_id}/note/{id}.{_format}




