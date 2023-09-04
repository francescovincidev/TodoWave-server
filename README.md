# Documentazione del Progetto TodoWave

# FrontEnd

### Tecnologie Utilizzate
- Vite
- Vue.js
- Bootstrap
- Sass
- Axios
- Vue Router
- Font Awesome

### Struttura
- Le pagine visitabili sono localizzate nella directory "pages".
- I componenti più complessi sono situati nella directory "components".
- All'interno della cartella "style", troverai un file SCSS generico, insieme a una sottocartella denominata "partials" contenente mixins e variabili.
- Per la gestione dei dati cruciali, è stato implementato uno "store".
- La gestione delle rotte del sito è affidata a un sistema di "router".

### HOME, LOGIN E REGISTRAZIONE
Abbiamo una pagina Home, con una breve introduzione al sito che ci permetterà di entrare con il nostro profilo o, eventualmente, di registrarci.

Se decidiamo di registrarci, verremo indirizzati nel form di registrazione. Qui tutti i campi sono obbligatori. L'utente può inserire un nome di almeno 2 caratteri fino ad un massimo di 20. Un'email già non registrata e una password da ripetere due volte (almeno 8 caratteri).

Tutte le validazioni vengono inizialmente gestite lato client, ma in caso di tentativi di manipolazione da parte dell'utente, vengono effettuate anche verifiche lato server. Lo stesso avviene durante la procedura di login, dove è sufficiente utilizzare l'email e la password per accedere. In entrambe le pagine, è possibile passare facilmente da una all'altra. Entrambi usano una chiamata POST axios per verificare e inviare i dati (verranno mandati sotto forma di json).

Il processo di login prevede il salvataggio del nome utente e del suo ID nello store.

Il logout avviene tramite lo svuotamento del nome utente, dell'ID, dei Todos e dei Tags.

### PAGINA PRINCIPALE (TODOS)
Una volta registrati o loggati, verremo indirizzati nella pagina principale, dove vedremo i nostri Todos (se presenti) grazie al componente `TodoList` e ci verrà notificato se abbiamo dei Todos che stanno per scadere. Il componente `TodoList` viene ripetuto due volte per creare due liste: Todos attivi e Todos scaduti.

I Todos completati saranno grigi e con il testo barrato, mentre quelli attivi o scaduti saranno verdi o rossi. Al click sui Todos, questi cambieranno colore per segnarli come completati (o non completati). Vengono aggiornati anche nel database tramite richiesta PUT. Le icone che troviamo nei Todos sono tre: un'icona "i" per aprire le informazioni di quel Todo, un'icona "!" gialla per indicare se il Todo sta per scadere o una rossa nei Todo scaduti e non completati (icone di Font Awesome).

A destra troviamo un menu a tendina con la lista di tutti i nostri Tags. Cliccando su di essi, i componenti `TodoList` verranno aggiornati e mostrati solo i Todos con quel determinato Tag.

In basso a sinistra troviamo un menu. Aprendolo avremo quattro opzioni: Aggiungi Todo, Aggiungi Tag, Elimina Tag e Logout.

### Componente TodoList
Nella pagina principale, il componente `TodoList` viene chiamato due volte: una per visualizzare i Todos attivi e un'altra per i Todos scaduti. I Todos vengono ottenuti dallo store centrale dell'applicazione, a meno che non vengano filtrati in base a un tag specifico. In caso di filtro, la pagina principale temporaneamente salverà i Todos con quel Tag e li passerà tramite props ai rispettivi componenti TodoList.

Oltre ai dati dei Todos, viene passata anche la funzione `getTodos` per ciascuna lista. Questa funzione consente di aggiornare la lista dei Todos in risposta all'azione dell'utente, quella di contrassegnare un Todo come completato o non completato. In questo modo, la lista rimane sempre aggiornata.

Il componente è composto da molti v-if e operatori ternari per far adattare il componente ai Todos attivi e quelli scaduti.

### Funzione getTodos
La funzione `getTodos` viene attivata al caricamento della pagina principale e dal componente `TodoList` per aggiornare i todos nello store. Una volta che ottiene i dati (con una richiesta GET, passando come parametro l'ID dell'utente), li passa ad un'altra funzione `refreshTodos` presente nello store, che divide i Todos in attivi e scaduti. Questo avviene tramite l'aggiunta di proprietà nell'oggetto Todos, aggiungendo "expired" se è scaduto o "upcomingExpiration" se sta scadendo (entro tre giorni). Non viene aggiunta nessuna proprietà per i Todos senza scadenza e con scadenza oltre i tre giorni.

### Creazione Todo
Cliccando sul pulsante "Aggiungi Todo", saremo rindirizzati alla pagina di creazione Todo, che condivide lo stesso form con la pagina di modifica. Qui, possiamo inserire le seguenti informazioni:
- Titolo (obbligatorio, da 3 a 100 caratteri).
- Descrizione (massimo 1000 caratteri).
- Scadenza (con possibilità di reimpostarla).
- Selezione dello stato (completato o non completato).
- Possibilità di aggiungere tags, se presenti nel nostro profilo.

Una volta compilato il form, è possibile fare clic su "Crea". Ciò genererà una richiesta POST che invierà i dati al database. Successivamente, verremo rindirizzati alla pagina principale con una notifica di conferma del caricamento. Tutte le validazioni saranno eseguite sia lato client che lato server.

### Modifica Todo
La modifica condivide lo stesso form della creazione (è un unico componente `TodoForm`). Qui le validazioni avvengono nella stessa maniera della creazione, le uniche differenze sono nella richiesta (che sarà PUT) e il fatto che il form sarà già compilato con i dati del Todo. Per la modifica, l'ID del Todo da modificare viene passato tramite router.

### Info Todo
Cliccando sull'icona "i" presente accanto a ciascun Todo nella lista, saremo rindirizzati alla pagina delle informazioni del Todo selezionato. Durante il caricamento, sfruttiamo l'ID associato al Todo (passato tramite il router) per filtrare i Todos nello store e recuperare le informazioni relative a quello selezionato. Qui, oltre al titolo già presente nella lista, potremo vedere tutte le altre informazioni (descrizione, scadenza, stato e Tags se presenti). Sarà possibile modificare o eliminare il Todo.

### Eliminazione Todo
Nella pagina delle informazioni del Todo, sarà possibile effettuare l'eliminazione del Todo. Al momento del clic su "Elimina", verrà aperto un modal Bootstrap che richiederà una conferma della nostra scelta. Se procediamo confermando l'azione, verrà effettuata una richiesta DELETE al server, utilizzando l'ID del Todo. Questa richiesta rimuoverà definitivamente il Todo dal database (dopo aver eliminato anche il collegamento con eventuali Tags).

### TAGS
Nel nostro sistema, ogni elemento "Todo" può essere associato a dei "Tags" per un'organizzazione più efficace. Gli utenti hanno la possibilità di creare fino a 10 Tags distinti da assegnare ai loro compiti e di utilizzarli come strumento di filtraggio.

### Aggiunta di un Tag
Nel menu in basso a sinistra, è presente un pulsante denominato "Aggiungi Tag". Cliccando su di esso, verrà visualizzato un modal basato su Bootstrap, che chiede semplicemente all'utente di inserire il nome del nuovo Tag. Si applicano alcune regole: il nome del Tag deve essere compreso tra 3 e 20 caratteri. Le validazioni vengono effettuate sia lato client che lato server. Se un utente ha già 10 Tags, il pulsante di aggiunta verrà disabilitato o, se l'utente riesce ad aprirlo, riceverà un messaggio di errore dal server.

### Eliminazione di un Tag
Un'altra funzionalità importante è la possibilità di eliminare i Tags. Il pulsante "Elimina Tag" verrà disabilitato se l'utente non ha ancora creato alcun Tag. Al contrario, se l'utente desidera rimuovere alcuni Tags, verrà aperto un modal contenente una lista di Tags con relativi checkbox. L'utente può selezionare più Tags da eliminare e confermare l'operazione.

Per supportare queste due funzionalità, la funzione `getTags` è passata tramite props ai componenti. Questa funzione, presente nella pagina principale, agisce in modo simile a `refreshTodos`, svuotando e aggiornando l'array dei Tags per garantire che l'interfaccia utente rimanga aggiornata.

### NOTIFICHE
Ogni volta che riceviamo dei messaggi dal backend (login avvenuto, todo creato, todo modificato, ecc.), viene comunicato tramite una piccola notifica che compare al centro della pagina, nella parte superiore, con sfondo verde. Stessa cosa accadrà per gli errori, ma con sfondo rosso (gli errori di validazione dati non sono compresi, questi compariranno nel form). Arriverà una notifica nella pagina principale se si hanno dei Todos in scadenza.

### NOT FOUND
Not found è una pagina a cui si viene reindirizzati se si inserisce un URL non valido. Qui verremo avvisati che la pagina non esiste e potremmo andare alla pagina di login o registrazione.

### LOGIN NON EFFETTUATO
Se si prova ad entrare in una delle pagine principali senza essere loggati, avremo una pagina che ci avvisa di ciò e che ci invita a registrarci o effettuare il login.

# BACKEND

Nel lato backend, che si basa interamente su PHP, abbiamo tre entità: `User`, `Todos` e `Tags`, che seguono una struttura comune. A ognuna di queste entità viene associata una classe per gestire le operazioni per ogni categoria: ogni classe viene estesa con una relativa classe di validazione per garantire la coerenza dei dati. Troviamo per ognuno poi anche un file dedicato alle relative richieste HTTP. Questo ci permette di avere coerenza e modularità nello sviluppo. Abbiamo anche un file di setup che tramite il `require_once` gestisce tutto il collegamento fra file. Troviamo anche una variabile `methods` che prende appunto il metodo della richiesta che effettuiamo. Nella cartella includes, troviamo il file Db.php, che contiene un trait con un metodo per stabilire una connessione al database MySQL. Inoltre, c'è il file CORS.php, che include una funzione per gestire le richieste CORS (Cross-Origin Resource Sharing). Infine, il file create_table.php è utilizzato per svuotare e aggiornare le tabelle del database MySQL.

## Introduzione alle entità
Ogni classe `_validation` contiene tutte le validazioni per la classe principale. Se la classe principale riceve errore, esce e passa l'errore al frontend. Il file `_endpoint.php` ci permette di gestire i metodi in base alla richiesta. Ogni query avviene tramite prepared statement.

## User

### User.php
La classe User è responsabile di due funzioni principali: la registrazione di nuovi utenti e l'accesso degli utenti esistenti. Nel costruttore passiamo username, email, password e passwordRepeat, per confrontare le due password. All'inizio di ogni metodo viene connesso al db tramite il trait `Db`.

Il metodo `registerUser` gestisce la registrazione di un nuovo utente nel database. Prima di procedere, vengono effettuate alcune verifiche dei dati inseriti tramite il metodo `registerUser_validation` presente nella classe delle validazioni. Se i dati non sono validi, viene restituita una risposta di errore. In caso contrario, la password viene criptata e l'utente viene inserito nel database con successo. Qui, come in tutte le query del progetto, viene utilizzato un sistema di prepared statement per prevenire l'iniezione di query.

Il metodo `loginUser`, invece, si occupa dell'autenticazione degli utenti. Simile a `registerUser`, verifica inizialmente se i dati inseriti sono validi e poi cerca l'utente nel database. Se le credenziali corrispondono, l'utente viene autenticato e riceve una risposta di successo. Qui vengono gestiti i messaggi per le notifiche del frontend (e gli errori di validazione).

### User_validation.php
La classe `User_validation` estende la classe `User`. Essa fornisce funzioni di validazione dei dati, che sono essenziali per garantire che i dati inseriti dagli utenti siano corretti e sicuri. 

Il metodo `registerUser_validation` valida i dati forniti durante la registrazione di un utente. Questo include la verifica che tutti i campi siano compilati, la lunghezza dell'username, la validità dell'indirizzo email e l'unicità dell'indirizzo email nel database. Se vengono rilevati errori, essi sono restituiti, e `User.php` passerà questi al frontend.

Il metodo `loginUser_validation` si occupa invece di validare i dati inseriti durante il processo di login. Assicura che l'indirizzo email sia valido e che tutti i campi siano compilati.


### user_enspoints.php
Questo file gestisce le richieste relative agli utenti, inclusa la registrazione e il login. In base al percorso della richiesta e al metodo HTTP utilizzato, reindirizza le richieste alla classe `User` per eseguire le operazioni corrispondenti. Una volta ricevuta la richiesta e il metodo converte i file (che arrivano in JSON) e dopo aver creato l'oggetto ne richiama il metodo in base alla richiesta.

## Todos

### Todo.php
In questo file gestiamo tutti metodi per le CRUD dei Todos. Anche questo viene esteso con una classe per le validazioni.	 

Abbiamo il metodo `getTodos` che resituisce tutti i todos filtrandoli per ID dell'user. Viene eseguito anche il metodo `getTagsForTodo` che assegna ai Todo i propri Tags.

Il metodo `getTagsForTodo` che non fa altro che prendere tutti i Tag di un Todo tramite l'ID di quest'ultimo. 

`upTags` che serve a caricare nel database i Tags che si selezionano durante la modifica/creazione di un Todo e a cancellare quelli che non vengono selezionati.

Poi troviamo `createTodo` che dopo la validazione, se non ci sono errori carica il Todo, usando anche `upTags` per i Tags del Todo. 

Cosa molto simile avviene in `updateTodo`. Infatti condivide con `createTodo` lo stesso metodo per la validazione dei dati. Qui come prima cosa vengono presi i dati del Todo da modificare, e se i dati mandati dall'utente sono effettivamente cambiati, esegue una query per l'aggiornamento. Otteniamo messaggi diversi in base alla modifica avvenuta o no.

`updateTodoCompleted` aggiorna solo lo stato del Todo, completato o no. Questo metodo viene serve per gestire il completato/non completato tramite il click su Todo.

`deleteTodo` si occupa dell'eliminazione del Todo. Come prima cosa elimina i collegamenti che il Todo con la tabella ponte todo_tag e poi procede all'effettiva eliminazione del Todo.


### Todo_validation.php
La classe `Todo_validation` estende la classe `Todo`. Qui troviamo un solo metodo che, come detto in precedenza, viene richiamato prima dell'effettiva creazione o modifica del Todo. Verifica che ci sia effettivamente l'ID dell'user, che il titolo sia compreso fra 3 e 100 caratteri, che la descrizione non sia oltre i 1000 caratteri, il formato della data della scadenza e se lo stato di completo sia diverso da 1 o 0. Se ci sono errori vengono passati a Todos.php e si esce dall'esecuzione del metodo

### todos_endpoint.php
In questo file, come in precedenza, vengono gestite le richieste, i dati ricevuti e il metodo e in base a questo viene deciso quale metodo eseguire.

## Tags

### Tags.php
Qui gestiamo la creazione, l'eliminazione dei Tags e il loro ottenimento.

Per quest'ulitmo usiamo il metodo `getTags` che passa tutti i Tags in base all'ID dell'user

`createTag`, dopo aver validato i dati carica l'ID dell'user e il nome del Tag.

`deleteTag`. A questo metodo viene passato un array di ID di Tags e l'ID dell'user. Come prima cosa cancella tutti i collegamenti con la tabella todo_tag tramite l'ID di ogni Tag e poi elimina i Tags dalla tabella tags tramite l'ID dei tags e dell'user.


### Tags_validation.php
Qui avvengono le validazione per i Tags. Deve arrivare l'ID dell'user, il nome del tag deve essere fra i 3 e 20 caratteri e controlla anche se l'utente ha già 10 tags.

### tags_endpoint.php
Gestisce le richieste, i dati ricevuti e il metodo.



# Database

Il database è formato da tre tabelle principali più una tabella ponte.
Abbiamo la tabella users con le seguenti colonne:
- user_id, la chiave primaria
- username, massimo 20 caratteri, not null
- email, not null, unique
- password not null, unique

Tabella  todos:
- todo_id, chiave primaria
- user_id, la chiave esterna, collega il todo all'user, not null
- title, massimo 100 caratteri, not null
- description, 1000 caratteri
- deadline, date
- completed, 1 o 0, not null default 0

Tabella tags:
- tag_id, chiave primaria 
- user_id, chiave esterna not null, collega all'user
- tag_name di massimo 20 caratteri

Tabella ponte todo_tag:
- todo_id
- tag_id

Quindi troviamo un collegamento uno a molti tra users  e todos. Stessa coaa tra users e tags. Abbiamo invece un collegamento molti a molti tra todos e tags tramite todo_tag

