from fastapi import FastAPI, Header, HTTPException, Depends, APIRouter
from dotenv import load_dotenv
import os
from groq import Groq
import warnings
from pydantic import BaseModel

# Suppress specific warning messages
warnings.filterwarnings("ignore", message="missing ScriptRunContext!")

# Initialize FastAPI app
router = APIRouter(
    prefix="/job",  # Prefix for routes
    responses={404: {"description": "Not found"}},
    tags=["Job Description based AI Generated Assessments"]
)

# Initialize Groq client (but will not use environment variables here)
client = None

# Function to initialize the Groq client using API key from the header
def get_groq_client(api_key: str):
    """Initialize and return the Groq client using the provided API key."""
    global client
    if not client:
        client = Groq(api_key=api_key)
    return client

# Pydantic models for request validation
class JobDescriptionRequest(BaseModel):
    job_description: str
    num_mcqs: int = 5
    num_tasks: int = 3
    model: str  # Accept model type as part of the request

# Function to generate MCQs based on job description
def generate_mcqs(job_desc, num_questions, groq_client, model):
    messages = [
        {"role": "system", "content": "You are a helpful assistant that generates MCQs based on job descriptions."},
        {"role": "user", "content": f"Please generate {num_questions} multiple choice questions based on the following job description: {job_desc}. Strictly follow the format below for each question:\n\n1. question text\n\n a. option 1\n b. option 2\n c. option 3\n d. option 4\n\nAlso, provide the correct answers based on the question numbers after generated of all mcqs. Do not add any extra text or formatting."}
    ]
    
    try:
        completion = groq_client.chat.completions.create(
            model=model,  # Use the model passed from the frontend
            messages=messages,
            temperature=1,
            max_tokens=1024,
        )
        
        # Accessing response content
        if completion.choices:
            return completion.choices[0].message.content  # Correctly access the content
        else:
            return "No MCQs generated. Please try again."
    except Exception as e:
        return f"Error: {str(e)}"

# Function to generate tasks based on job description
def generate_tasks(job_desc, num_tasks, groq_client, model):
    messages = [
        {"role": "system", "content": "You are a helpful assistant that generates tasks based on job descriptions."},
        {"role": "user", "content": f"Please generate {num_tasks} tasks based on the following job description: {job_desc}"}
    ]
    
    try:
        completion = groq_client.chat.completions.create(
            model=model,  # Use the model passed from the frontend
            messages=messages,
            temperature=1,
            max_tokens=1024,
        )
        
        # Accessing response content
        if completion.choices:
            return completion.choices[0].message.content  # Correctly access the content
        else:
            return "No tasks generated. Please try again."
    except Exception as e:
        return f"Error: {str(e)}"

# POST endpoint to generate MCQs
@router.post("/generate_mcqs/")
async def generate_mcqs_endpoint(
    request: JobDescriptionRequest,
    api_key: str = Header(...),  # Accept the API key from headers
):
    # Initialize the Groq client with the provided API key
    groq_client = get_groq_client(api_key)
    
    job_description = request.job_description
    num_mcqs = request.num_mcqs
    model = request.model  # Get model from request body
    
    if not job_description:
        raise HTTPException(status_code=400, detail="Job description is required.")
    
    mcqs = generate_mcqs(job_description, num_mcqs, groq_client, model)
    return {"mcqs": mcqs}

# POST endpoint to generate tasks
@router.post("/generate_tasks/")
async def generate_tasks_endpoint(
    request: JobDescriptionRequest,
    api_key: str = Header(...),  # Accept the API key from headers
):
    # Initialize the Groq client with the provided API key
    groq_client = get_groq_client(api_key)
    
    job_description = request.job_description
    num_tasks = request.num_tasks
    model = request.model  # Get model from request body
    
    if not job_description:
        raise HTTPException(status_code=400, detail="Job description is required.")
    
    tasks = generate_tasks(job_description, num_tasks, groq_client, model)
    return {"tasks": tasks}
