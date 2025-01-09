from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from routers import job_description_based_ai_questions

app = FastAPI()

# CORS setup
origins = [
    "http://127.0.0.1/lms.atlearn.in/",  # Allows requests from localhost (change as needed)
    "http://localhost:8000",  # Allows requests from localhost on port 8000
    "https://lms.atlearn.in/",  # Add any other domains you want to allow
]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,  # Or use `["*"]` to allow all origins
    allow_credentials=True,
    allow_methods=["*"],  # Allows all methods (GET, POST, etc.)
    allow_headers=["*"],  # Allows all headers
)

# Include routers
app.include_router(job_description_based_ai_questions.router)

