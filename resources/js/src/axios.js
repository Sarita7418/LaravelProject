import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true, // Necesario para cookies de Sanctum
});

export default api;
