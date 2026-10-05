IT490 APU Research
API : PoetryDB

1. API Name: PoetryDB
2. API Repository / Documentaion : https://github.com/thundercomb/poetrydb
3. API Endpoint: https://poetrydb.org/
4. What is PoetryDB?
It is an API that provides poetry data in JSON format.
4. What information foes the API provide?
 - Poem title
 - Author
 - Poem lines
 - Number of lines
 - Source
 - Author information
5. API Endpoints Tested

#### Random poem: 

https://poetrydb.org/random

This endpoint returns a random poem.

#### Multiple random poems

https://poetrydb.org/random/3

This endpoint returns three random poems.

#### Specific poem

https://poetrydb.org/title/Ozymandias

This endpoint searches for the poem "Ozymandias".

#### Author information

https://poetrydb.org/author/Dickinson/info

This endpoint provides information about Emily Dickinson.

### 6. How the API Works

The user or website sends a request to a PoetryDB URL.

PoetryDB responds with information about the requested poem or author.

The information is returned in JSON format.

### 7. Initial Website Idea

A poetry discovery website where users can search for poems, discover random poems, read poems, and learn about authors.

### 8. Why Would Users Create an Account?

Users could create an account to save their favorite poems.

They could return to the website later and view their saved poems instead of searching for them again.

The account could also allow users to organize their favorite poems into personal collections.

### 9. Update Frequency

The project requires an API that receives updates semi-frequently.

PoetryDB is a live API, but more research is needed to confirm whether its underlying poetry data is updated frequently enough to satisfy this requirement.


